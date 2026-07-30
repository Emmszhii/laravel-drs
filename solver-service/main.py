from fastapi import FastAPI
from app.api import optimize
from app.core.logging import configure_logging

configure_logging()

app = FastAPI(
    title="DisasterResource+ Optimization Service",
    description="Allocation and routing solver microservice for DisasterResource+.",
    version="0.1.0",
)

app.include_router(optimize.router, prefix="/api/v1/optimize", tags=["optimize"])


@app.get("/health")
def health():
    """Simple health check — wire into Laravel/uptime monitoring before the demo."""
    return {"status": "ok"}
