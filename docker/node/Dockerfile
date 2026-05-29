FROM node:22-bookworm-slim

WORKDIR /var/www/html

COPY package*.json ./
RUN npm ci --silent

COPY . .

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
