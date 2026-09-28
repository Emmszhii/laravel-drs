from fastapi import FastAPI
from app.api import optimize, replenishment
from app.core.logging import configure_logging

configure_logging()

app = FastAPI(
    title="DisasterResource+ Optimization Service",
    description="Allocation and routing solver microservice for DisasterResource+.",
    version="0.1.0",
)

app.include_router(optimize.router, prefix="/api/v1/optimize", tags=["optimize"])
app.include_router(replenishment.router, prefix="/api/v1/replenishment", tags=["replenishment"])


@app.get("/health")
def health():
    """Health check consumed by Docker/Compose and the backend's depends_on condition."""
    return {"status": "ok"}
