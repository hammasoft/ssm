# syntax=docker/dockerfile:1.7
# Image multi-stage commune aux deux services : docker build --build-arg MODULE=marketplace-service .
ARG MODULE=marketplace-service

FROM maven:3.9-eclipse-temurin-21 AS build
ARG MODULE
WORKDIR /workspace
COPY pom.xml .
COPY revivo-events/pom.xml revivo-events/
COPY marketplace-service/pom.xml marketplace-service/
COPY notification-service/pom.xml notification-service/
RUN --mount=type=cache,target=/root/.m2 mvn -B -q -pl ${MODULE} -am dependency:go-offline
COPY . .
RUN --mount=type=cache,target=/root/.m2 mvn -B -q -pl ${MODULE} -am package -DskipTests \
    && java -Djarmode=tools -jar ${MODULE}/target/${MODULE}.jar extract --layers --launcher --destination /extracted

FROM eclipse-temurin:21-jre
RUN groupadd --system app && useradd --system --gid app --home /app app \
    && mkdir -p /app/data/uploads && chown -R app:app /app
WORKDIR /app
COPY --from=build --chown=app:app /extracted/dependencies/ ./
COPY --from=build --chown=app:app /extracted/spring-boot-loader/ ./
COPY --from=build --chown=app:app /extracted/snapshot-dependencies/ ./
COPY --from=build --chown=app:app /extracted/application/ ./
USER app
ENV JAVA_TOOL_OPTIONS="-XX:MaxRAMPercentage=75 -XX:+UseG1GC" UPLOAD_DIR=/app/data/uploads
ENTRYPOINT ["java", "org.springframework.boot.loader.launch.JarLauncher"]
