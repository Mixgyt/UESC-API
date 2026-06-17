# Plan de Implementación: Visor Gráfico de Mempool en Tiempo Real

Este plan describe los cambios necesarios para diseñar, construir e integrar un visor gráfico interactivo de la mempool en tiempo real. La vista será accesible desde la landing page principal (`/`).

## Diseño Visual y Experiencia
Para lograr una estética premium y moderna que encaje con el estilo actual:
1. **Gráfico de la Mempool**: Se implementará un visualizador interactivo (usando HTML5 Canvas o cuadrícula animada DOM) que muestre las transacciones pendientes en la mempool representadas como bloques de colores de acuerdo a su tasa de comisión (sat/vB), similar a la interfaz de *mempool.space*.
2. **Estadísticas en Tiempo Real**: Panel lateral o superior con datos clave (conteo de transacciones, tamaño virtual total en vBytes, promedio de fee rates, y comisiones acumuladas) que se actualice dinámicamente mediante consultas AJAX (`fetch`) cada pocos segundos sin necesidad de recargar la página.
3. **Interactividad**: Al pasar el cursor sobre cualquier transacción del gráfico se mostrará un tooltip informativo con su `txid`, `fee_sat`, `vsize`, `fee_rate` y dependencias. Al hacer clic, se podrá ver su detalle completo.

---

## Cambios Propuestos

### Componente Frontend & Rutas

#### [NEW] [mempool.blade.php](file:///home/mixgyt/Development/PHP/UESC-api/resources/views/mempool.blade.php)
Crear una nueva vista Blade dedicada para el visor de la mempool.
* **Estructura**:
  * Contenedor principal alineado con el diseño oscuro premium actual (uso de fuentes Outfit/JetBrains Mono, paleta de colores HSL/oklch, degradados sutiles).
  * Sección del gráfico principal (visualización de la mempool proyectada en bloques o cubos interactivos).
  * Panel de detalles/resumen con micro-animaciones en los números al actualizarse.
* **Lógica (JS integrado o cargado con Vite)**:
  * Consultas periódicas a `/api/mempool` y `/api/mempool/summary` usando `setInterval` (por ejemplo, cada 10 segundos).
  * Renderizado interactivo de las transacciones (la intensidad del color naranja/rojo variará según el *fee_rate* y el tamaño del bloque visual dependerá del *vsize*).

#### [MODIFY] [welcome.blade.php](file:///home/mixgyt/Development/PHP/UESC-api/resources/views/welcome.blade.php)
* Integrar un nuevo enlace/botón destacado ("Ver Mempool en Vivo") en la barra de navegación (header) y en la sección Hero para dirigir al usuario a la nueva vista de la mempool.
* Agregar información rápida o un widget simplificado del mempool en la sección de estadísticas actuales.

#### [MODIFY] [web.php](file:///home/mixgyt/Development/PHP/UESC-api/routes/web.php)
* Registrar la nueva ruta `/mempool` que retorne la vista `mempool.blade.php`:
  ```php
  Route::get('/mempool', function () {
      return view('mempool');
  });
  ```

---

## Plan de Verificación

### Verificación Manual
1. **Acceso**: Verificar que desde la landing page (`/`) se pueda hacer clic en el enlace/botón y redirigir correctamente a `/mempool`.
2. **Interactividad**: Validar que al cargar la mempool con datos de prueba, la interfaz dibuje las transacciones como bloques/esferas interactivas con tooltip al pasar el mouse.
3. **Actualización en tiempo real**: Probar insertando transacciones en la mempool de regtest y confirmar que aparezcan reflejadas en la UI dentro de unos segundos de forma fluida.
