## Registro de usuario


### Endpoint
`POST /api/register`

### Payload requerido
El frontend debe enviar el siguiente JSON para que el registro sea exitoso:

```json
{
  "name": "Carlos Perez",
  "email": "carlos@example.com",
  "password": "123456",
  "password_confirmation": "123456",
  "identification_type_code": "dni",
  "number": "72123456",
  "issued_at": "2020-01-01",
  "expires_at": "2030-01-01"
}


```bash
php artisan migrate

