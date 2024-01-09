apiVersion: v1
kind: Secret
metadata:
    name: secret-basic-auth
type: kubernetes.io/basic-auth
stringData:
    username: "${BASIC_AUTH_USER}"
    password: "${BASIC_AUTH_PASS}"
