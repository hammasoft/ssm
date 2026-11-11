package com.revivo.marketplace.user;

import org.junit.jupiter.api.Test;

import static org.assertj.core.api.Assertions.assertThat;

class UserRatingTest {

    @Test
    void ratingAverageIsUpdatedIncrementally() {
        User user = new User("alice", "alice@test.dev", "hash");
        user.addRating(5);
        user.addRating(4);
        user.addRating(4);

        assertThat(user.getRatingCount()).isEqualTo(3);
        assertThat(user.getRatingAvg()).isEqualByComparingTo("4.33");
    }
}
