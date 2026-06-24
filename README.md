# PruebaTecnica-Backend

API REST para un sistema de pedidos construida con Laravel 10. Usa autenticación con Sanctum y corre sobre Docker a través de Laravel Sail.

---

## Requisitos previos

Tener instalado Docker Desktop y tenerlo corriendo antes de ejecutar cualquier comando. En Windows se recomienda usar la terminal de Ubuntu (WSL).

---

## Instalación

Clonar el repositorio y entrar a la carpeta:

```bash
git clone <url-del-repo>
cd PruebaTecnica-Backend
```

Instalar dependencias de PHP:

```bash
composer install --ignore-platform-reqs
```

Copiar el archivo de variables de entorno:

```bash
cp .env.example .env
```

Levantar los contenedores con Sail:

```bash
./vendor/bin/sail up -d
```

Generar la clave de la aplicación:

```bash
./vendor/bin/sail artisan key:generate
```

Correr las migraciones y los seeders de una vez:

```bash
./vendor/bin/sail artisan migrate --seed
```

La aplicación queda disponible en http://localhost.

Para detener los contenedores:

```bash
./vendor/bin/sail down
```

---

## Datos de prueba (seeder)

El seeder crea automáticamente un usuario y 10 productos con stock disponible.

```
Email:    test@example.com
Password: password
```

---

## Cómo probar los endpoints

Se puede usar Postman, Insomnia o la extensión Thunder Client de VS Code. Todos los requests deben incluir el header `Accept: application/json`.

El flujo básico es: registrarse o hacer login para obtener el token, y luego usar ese token en los endpoints que lo requieren.

---

## Endpoints

### POST /api/register

Registra un usuario nuevo y devuelve un token.

Body:
```json
{
  "name": "Christian",
  "email": "christian@test.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

---

### POST /api/login

Inicia sesión y devuelve un token.

Body:
```json
{
  "email": "test@example.com",
  "password": "password"
}
```

A partir de acá, todos los endpoints requieren el token en el header:

```
Authorization: Bearer <token-obtenido-en-login>
```

---

### GET /api/products

Lista todos los productos disponibles con su ID, nombre, precio y stock.

---

### GET /api/orders

Lista todos los pedidos del usuario autenticado.
---

### POST /api/orders

Crea un pedido nuevo.

Body:
```json
{
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 3, "quantity": 1 }
  ]
}
```

El total se calcula automáticamente. Si algún producto no tiene stock suficiente el pedido no se crea y devuelve 422.

---

### GET /api/orders/{id}

Devuelve el detalle de un pedido con sus items y productos. Solo funciona si el pedido le pertenece al usuario autenticado, de lo contrario devuelve 403.

Sin body.

---

### PUT /api/orders/{id}/cancel

Cancela un pedido. Solo funciona si el pedido está en estado pending. Si ya está completado o cancelado devuelve 422.

Sin body.

---

## Errores comunes al probar

Si devuelve 401, el token no está incluido o ya expiró, hay que hacer login de nuevo.

Si devuelve 403 al ver o cancelar un pedido, ese pedido pertenece a otro usuario.

Si devuelve 422 al crear un pedido, revisar que los product_id existan y que la cantidad no supere el stock disponible.