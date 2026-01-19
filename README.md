# encrypted_chatroom
A proof-of-concept real-time web messaging app with end-to-end encryption

Regenerate wayfinder files (routes) before build : `php artisan wayfinder:generate --with-form`

openssl generation string : `docker compose run --rm certificate_generator req -x509 -nodes -days 365 -newkey rsa:2048 -keyout ./self.key -out ./self.crt -subj /C=FR/ST=Dev/L=Local/O=LocalDev/OU=Dev/CN=100.124.238.99`

Front end inspiration : https://v0.app/chat/shadcn-style-chat-ui-buQVA0rcaSG
