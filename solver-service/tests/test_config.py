"""
Settings env-prefix (SOLVER_) behavior — defaults and override-via-env-var.
"""
from app.core.config import Settings


def test_settings_defaults():
    settings = Settings(_env_file=None)
    assert settings.app_name == "DisasterResource+ Optimization Service"
    assert settings.environment == "local"
    assert settings.log_level == "INFO"
    assert settings.metrics_enabled is False
    assert settings.osrm_url == "http://osrm:5000"


def test_settings_reads_solver_prefixed_env_vars(monkeypatch):
    monkeypatch.setenv("SOLVER_ENVIRONMENT", "production")
    monkeypatch.setenv("SOLVER_LOG_LEVEL", "DEBUG")
    monkeypatch.setenv("SOLVER_OSRM_URL", "http://osrm-prod:5000")
    monkeypatch.setenv("SOLVER_METRICS_ENABLED", "true")

    settings = Settings(_env_file=None)

    assert settings.environment == "production"
    assert settings.log_level == "DEBUG"
    assert settings.osrm_url == "http://osrm-prod:5000"
    assert settings.metrics_enabled is True


def test_settings_ignores_unprefixed_env_vars(monkeypatch):
    monkeypatch.setenv("ENVIRONMENT", "should-not-apply")

    settings = Settings(_env_file=None)

    assert settings.environment == "local"
