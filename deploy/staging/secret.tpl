apiVersion: v1
kind: Secret
metadata:
  name: justice-gov-uk-staging-secrets
type: Opaque
stringData:
  GOV_NOTIFY_API_KEY: "${GOV_NOTIFY_API_KEY}"
  AUTH_KEY: "${AUTH_KEY}"
  AUTH_SALT: "${AUTH_SALT}"
  LOGGED_IN_KEY: "${LOGGED_IN_KEY}"
  LOGGED_IN_SALT: "${LOGGED_IN_SALT}"
  NONCE_KEY: "${NONCE_KEY}"
  NONCE_SALT: "${NONCE_SALT}"
  SECURE_AUTH_KEY: "${SECURE_AUTH_KEY}"
  SECURE_AUTH_SALT: "${SECURE_AUTH_SALT}"
