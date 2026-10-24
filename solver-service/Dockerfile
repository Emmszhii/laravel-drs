FROM python:3.11-slim

WORKDIR /app

# Every requirement installs from a prebuilt wheel (pulp bundles its CBC binary),
# so no compiler; the health check uses Python's urllib instead of curl.
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

EXPOSE 8001

CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8001"]

HEALTHCHECK --interval=10s --timeout=3s --start-period=10s --retries=5 \
    CMD python -c "import urllib.request; urllib.request.urlopen('http://localhost:8001/health', timeout=2)"
