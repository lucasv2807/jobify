FROM node:22-alpine AS build

WORKDIR /app

COPY package.json ./
RUN npm install --omit=dev

COPY . .
ARG VITE_API_URL
ARG VITE_MP_CLIENT_ID
ARG VITE_API_PROXY_TARGET
ENV VITE_API_URL=$VITE_API_URL
ENV VITE_MP_CLIENT_ID=$VITE_MP_CLIENT_ID
ENV VITE_API_PROXY_TARGET=$VITE_API_PROXY_TARGET
RUN npm run build

FROM nginx:1.29-alpine-slim

COPY --from=build /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/nginx.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]