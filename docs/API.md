# Documentación de la API

Esta es la documentación oficial de la API del sistema, desarrollado en Laravel 12. La API utiliza autenticación basada en tokens (Laravel Sanctum) y soporta arquitectura multi-tenant (múltiples agencias/inquilinos).

## Autenticación

Todas las rutas protegidas requieren que se envíe el token de acceso en las cabeceras de la petición:

```http
Authorization: Bearer <tu_token>
```

## Multi-Tenancy

Para acceder a las rutas que pertenecen a un tenant (agencia) en específico, se puede utilizar uno de los siguientes métodos:

1. **Por Ruta (Path):** Agregando el identificador (slug o id) del tenant en la URL.
   - Ejemplo: `/api/{tenant}/iconos`
2. **Por Cabecera (Header):** Enviando la cabecera `X-Tenant` con el identificador del tenant.
   - Ejemplo: `/api/iconos` con la cabecera `X-Tenant: {tenant}`

---

## 1. Rutas Centrales (Sin contexto de Tenant)

### Públicas

#### Verificar estado del sistema
- **URL:** `/api/estado`
- **Método:** `GET`
- **Descripción:** Verifica que el sistema esté funcionando correctamente.

#### Crear el primer administrador
- **URL:** `/api/primer-admin`
- **Método:** `POST`
- **Descripción:** Inicializa el sistema creando el primer usuario administrador.

#### Iniciar Sesión
- **URL:** `/api/login` o `/api/{tenant}/login`
- **Método:** `POST`
- **Cuerpo de la Petición:**
  ```json
  {
    "email": "usuario@ejemplo.com",
    "clave": "tu_contraseña"
  }
  ```
- **Respuesta Exitosa:**
  ```json
  {
    "success": true,
    "token": "1|abcdef...",
    "usuario": {
      "email": "usuario@ejemplo.com",
      "nombre": "Nombre",
      "rol": "admin",
      "empresaId": "uuid...",
      "empresaNombre": "Nombre de Empresa",
      "tenantId": "uuid...",
      "tenant_slug": "slug",
      "puedeEliminar": true
    }
  }
  ```

#### Ver Invitación Pendiente
- **URL:** `/api/invitar/{token}`
- **Método:** `GET`
- **Descripción:** Obtiene los detalles de una invitación mediante su token único.

#### Aceptar Invitación
- **URL:** `/api/invitar/{token}/aceptar`
- **Método:** `POST`
- **Cuerpo de la Petición:** (Requerido solo si el usuario no existe)
  ```json
  {
    "nombre": "Juan Pérez",
    "clave": "nueva_contraseña",
    "clave_confirmation": "nueva_contraseña"
  }
  ```
- **Descripción:** Acepta una invitación y registra o reasigna al usuario al tenant correspondiente.

---

## 2. Perfil y Sesiones (Rutas Centrales / Tenant Protegidas)

Estas rutas pueden accederse de forma central o dentro de un tenant.

#### Obtener datos del usuario actual
- **URL:** `/api/me` (o `/api/{tenant}/me`)
- **Método:** `GET`

#### Cerrar Sesión
- **URL:** `/api/logout` (o `/api/{tenant}/logout`)
- **Método:** `POST`

#### Actualizar Perfil
- **URL:** `/api/perfil` (o `/api/{tenant}/perfil`)
- **Método:** `PUT`
- **Cuerpo:**
  ```json
  {
    "nombre": "Nuevo Nombre",
    "email": "nuevo@email.com"
  }
  ```

#### Cambiar Contraseña
- **URL:** `/api/perfil/password` (o `/api/cambiar-clave`)
- **Método:** `PUT` / `POST`
- **Cuerpo:**
  ```json
  {
    "clave": "contraseña_actual",
    "nuevaClave": "nueva_contraseña"
  }
  ```

#### Listar Sesiones Activas
- **URL:** `/api/perfil/sesiones`
- **Método:** `GET`

#### Revocar Otras Sesiones (excepto la actual)
- **URL:** `/api/perfil/sesiones`
- **Método:** `DELETE`

#### Revocar una Sesión Específica
- **URL:** `/api/perfil/sesiones/{tokenId}`
- **Método:** `DELETE`

---

## 3. Rutas del Tenant (Protegidas)

Estas rutas aplican para el contexto de una agencia (tenant) en específico.

#### Obtener información del Tenant actual
- **URL:** `/api/{tenant}/tenant-info` (o enviando el header `X-Tenant`)
- **Método:** `GET`
- **Descripción:** Devuelve el nombre, slug y ID del tenant actual.

### Gestión de Carpetas
- **URL Base:** `/api/{tenant}/carpetas`
- **Métodos disponibles:**
  - `GET /`: Lista todas las carpetas.
  - `POST /`: Crea una nueva carpeta (requiere `nombre`).
  - `PUT /{id}`: Renombra una carpeta (requiere `nombre`).
  - `DELETE /{id}`: Elimina una carpeta (debe estar vacía).
- **Reordenar Carpetas:**
  - **URL:** `/api/{tenant}/carpetas/reorder`
  - **Método:** `PUT`
  - **Cuerpo:**
    ```json
    {
      "carpetas": [
        {"id": "uuid-1", "orden": 1},
        {"id": "uuid-2", "orden": 2}
      ]
    }
    ```

### Gestión de Íconos
- **URL Base:** `/api/{tenant}/iconos`
- **Métodos disponibles:**
  - `GET /`: Lista todos los íconos de la empresa actual.
  - `POST /`: Sube/registra un nuevo ícono (requiere `carpetaId`, `url`, `nombre`).
  - `PUT /{id}`: Actualiza la etiqueta del ícono (requiere `nuevaEtiqueta`, permisos: editar).
  - `DELETE /{id}`: Elimina el ícono (permisos: eliminar).
- **Registrar Click en Ícono:**
  - **URL:** `/api/{tenant}/iconos/{id}/click`
  - **Método:** `POST`
- **Reordenar Íconos:**
  - **URL:** `/api/{tenant}/iconos/reorder`
  - **Método:** `PUT`
  - **Cuerpo:**
    ```json
    {
      "iconos": [
        {"id": "uuid-1", "orden": 1},
        {"id": "uuid-2", "orden": 2}
      ]
    }
    ```

### Dashboard y Estadísticas (Solo Admin del Tenant)
- **URL:** `/api/{tenant}/dashboard`
- **Método:** `GET`
- **Descripción:** Retorna los últimos íconos subidos, los más copiados y los usuarios más activos.

### Usuarios e Invitaciones (Solo Admin del Tenant)
- **Listar Usuarios:** `GET /api/{tenant}/usuarios`
- **Crear Usuario:** `POST /api/{tenant}/usuarios`
- **Actualizar Usuario:** `PUT /api/{tenant}/usuarios/{id}`
- **Eliminar Usuario:** `DELETE /api/{tenant}/usuarios/{id}`

- **Listar Invitaciones:** `GET /api/{tenant}/invitaciones`
- **Crear Invitación:** `POST /api/{tenant}/invitaciones` (requiere `email`, `rol`). Envía correo electrónico.
- **Cancelar Invitación:** `DELETE /api/{tenant}/invitaciones/{id}`

### Empresas (Solo Admin del Tenant)
- **Listar Empresas:** `GET /api/{tenant}/empresas`
- **Crear Empresa:** `POST /api/{tenant}/empresas`
- **Eliminar Empresa:** `DELETE /api/{tenant}/empresas/{id}`

---

## 4. Rutas del Super Administrador

Rutas exclusivas para los usuarios con el rol `super-admin`. Se manejan globalmente sin depender de un tenant.

- **URL Base:** `/api/super-admin`

### Gestión de Tenants (Agencias)
- `GET /tenants`: Lista todos los tenants con sus dominios y suscripciones.
- `POST /tenants`: Crea una nueva agencia (tenant) y genera su token API.
- `PUT /tenants/{id}`: Actualiza los datos y dominio de una agencia.
- `DELETE /tenants/{id}`: Elimina una agencia.
- `POST /tenants/{id}/suspender`: Pasa a estado "suspendido".
- `POST /tenants/{id}/activar`: Pasa a estado "activo".

### Gestión de Planes y Suscripciones
- `GET /planes`: Lista todos los planes disponibles.
- `POST /planes`: Crea un nuevo plan.
- `PUT /planes/{id}`: Actualiza un plan.
- `DELETE /planes/{id}`: Elimina un plan.

- `POST /suscripciones`: Crea o asigna una suscripción a un tenant.
- `PUT /suscripciones/{id}`: Actualiza una suscripción existente.

### Listar Usuarios Globales
- `GET /usuarios`: Lista todos los usuarios registrados en el sistema (excepto súper-admins).
