# LanguageTool sidecar for Render.
#
# We wrap the official silviof image rather than referencing it directly
# because Render's Docker env auto-injects $PORT and expects the container
# to bind to it. The upstream LT image listens on 8010 unconditionally —
# this wrapper makes it honour $PORT so Render's health checks succeed.
FROM silviof/docker-languagetool:latest

# Render sets PORT at runtime; bind LT to it via the documented env var.
# Falls back to 8010 for local docker-compose use.
ENV LT_HTTPS_PORT=8010
EXPOSE 8010

# The base image already has an ENTRYPOINT that starts LT on 8010. We rebind
# $PORT to 8010 internally and tell Render's healthcheck where to look.
CMD ["sh", "-c", "exec java -cp /LanguageTool/languagetool-server.jar org.languagetool.server.HTTPServer --public --allow-origin '*' --port ${PORT:-8010}"]
