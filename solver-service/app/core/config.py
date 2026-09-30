from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_prefix="SOLVER_")

    app_name: str = "DisasterResource+ Optimization Service"
    environment: str = "local"
    log_level: str = "INFO"

    # Metrics/observability — wire up Prometheus or similar before production.
    metrics_enabled: bool = False


settings = Settings()
