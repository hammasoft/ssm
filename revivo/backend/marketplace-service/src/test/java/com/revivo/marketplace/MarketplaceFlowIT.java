package com.revivo.marketplace;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.revivo.marketplace.outbox.OutboxEventRepository;
import com.revivo.marketplace.outbox.OutboxRelay;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.AutoConfigureMockMvc;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.boot.testcontainers.service.connection.ServiceConnection;
import org.springframework.http.MediaType;
import org.springframework.test.context.TestPropertySource;
import org.springframework.test.context.bean.override.mockito.MockitoBean;
import org.springframework.test.web.servlet.MockMvc;
import org.testcontainers.containers.PostgreSQLContainer;
import org.testcontainers.junit.jupiter.Container;
import org.testcontainers.junit.jupiter.Testcontainers;

import static org.assertj.core.api.Assertions.assertThat;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

/**
 * Test d'intégration bout-en-bout du parcours d'achat sur une vraie base PostgreSQL
 * (Testcontainers) : inscription → annonce → offre → achat → expédition → validation → avis.
 * Ignoré automatiquement si Docker n'est pas disponible.
 */
@Testcontainers(disabledWithoutDocker = true)
@SpringBootTest
@AutoConfigureMockMvc
@TestPropertySource(properties = {"revivo.demo-data=false", "revivo.storage.upload-dir=target/test-uploads",
        "spring.kafka.admin.auto-create=false"})
class MarketplaceFlowIT {

    @Container
    @ServiceConnection
    static PostgreSQLContainer<?> postgres = new PostgreSQLContainer<>("postgres:16-alpine");

    /** Kafka n'est pas nécessaire ici : on vérifie le contenu de l'outbox. */
    @MockitoBean
    OutboxRelay outboxRelay;

    @Autowired MockMvc mvc;
    @Autowired ObjectMapper mapper;
    @Autowired OutboxEventRepository outbox;

    @Test
    void completePurchaseJourney() throws Exception {
        String sellerToken = register("seller1", "seller1@test.dev");
        String buyerToken = register("buyer1", "buyer1@test.dev");

        JsonNode item = call(post("/api/items").header("Authorization", "Bearer " + sellerToken)
                .contentType(MediaType.APPLICATION_JSON).content("""
                        {"title":"Veste en jean","description":"Très belle veste, peu portée",
                         "categoryId":12,"brandId":1,"size":"M","color":"Bleu","condition":"VERY_GOOD",
                         "price":20.00,"photoUrls":["/uploads/test.jpg"]}"""), 201);
        long itemId = item.get("id").asLong();

        mvc.perform(get("/api/items").param("q", "jean")).andExpect(status().isOk())
                .andExpect(jsonPath("$.totalElements").value(1));

        JsonNode offer = call(post("/api/items/" + itemId + "/offers").header("Authorization", "Bearer " + buyerToken)
                .contentType(MediaType.APPLICATION_JSON).content("{\"amount\":15}"), 201);
        call(post("/api/offers/" + offer.get("id").asLong() + "/accept")
                .header("Authorization", "Bearer " + sellerToken), 200);

        JsonNode order = call(post("/api/items/" + itemId + "/checkout").header("Authorization", "Bearer " + buyerToken)
                .contentType(MediaType.APPLICATION_JSON).content("""
                        {"shippingOption":"RELAY_POINT","shippingAddress":"Relais 42, Lyon","paymentMethodToken":"tok_visa"}"""), 201);
        long orderId = order.get("id").asLong();
        assertThat(order.get("itemPrice").decimalValue()).isEqualByComparingTo("15.00");
        assertThat(order.get("total").decimalValue()).isEqualByComparingTo("19.59");

        // un second achat du même article est refusé
        mvc.perform(post("/api/items/" + itemId + "/checkout").header("Authorization", "Bearer " + buyerToken)
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"shippingOption\":\"HAND_DELIVERY\",\"paymentMethodToken\":\"tok_visa\"}"))
                .andExpect(status().isConflict());

        call(get("/api/wallet").header("Authorization", "Bearer " + sellerToken), 200);
        call(post("/api/orders/" + orderId + "/ship").header("Authorization", "Bearer " + sellerToken)
                .contentType(MediaType.APPLICATION_JSON).content("{\"trackingNumber\":\"TRK-1\"}"), 200);
        call(post("/api/orders/" + orderId + "/confirm").header("Authorization", "Bearer " + buyerToken), 200);

        JsonNode wallet = call(get("/api/wallet").header("Authorization", "Bearer " + sellerToken), 200);
        assertThat(wallet.get("available").decimalValue()).isEqualByComparingTo("15.00");
        assertThat(wallet.get("pending").decimalValue()).isEqualByComparingTo("0");

        call(post("/api/orders/" + orderId + "/review").header("Authorization", "Bearer " + buyerToken)
                .contentType(MediaType.APPLICATION_JSON).content("{\"rating\":5,\"comment\":\"Parfait\"}"), 201);

        assertThat(outbox.findAll()).extracting(e -> e.getEventType()).contains(
                "UserRegistered", "ItemPublished", "OfferMade", "OfferAnswered", "OrderPlaced",
                "OrderStatusChanged", "ReviewPosted");
    }

    private String register(String username, String email) throws Exception {
        JsonNode auth = call(post("/api/auth/register").contentType(MediaType.APPLICATION_JSON)
                .content("{\"username\":\"%s\",\"email\":\"%s\",\"password\":\"Password123!\"}"
                        .formatted(username, email)), 201);
        return auth.get("accessToken").asText();
    }

    private JsonNode call(org.springframework.test.web.servlet.RequestBuilder request, int expectedStatus)
            throws Exception {
        String body = mvc.perform(request).andExpect(status().is(expectedStatus))
                .andReturn().getResponse().getContentAsString();
        return mapper.readTree(body);
    }
}
