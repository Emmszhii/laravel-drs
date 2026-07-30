import logging
from app.core.config import settings


def configure_logging() -> None:
    """
    Structured logging setup. Swap the basic formatter for
    python-json-logger's JsonFormatter once shipping logs to a
    centralized collector (ELK, Loki, etc.) becomes relevant.
    """
    logging.basicConfig(
        level=getattr(logging, settings.log_level.upper(), logging.INFO),
        format="%(asctime)s | %(levelname)s | %(name)s | %(message)s",
    )
