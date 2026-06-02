# TMPC_ColourOptions class

**Purpose:**  
Register colour option REST routes and connect them to the service layer.

## Expected Behavior
- Routes are registered at `plugins_loaded` using `register_rest_route()`.
- `GET /tmpc/v1/colour-options` is routed to service retrieval logic.
- `POST /tmpc/v1/colour-options-update` is routed to update logic.