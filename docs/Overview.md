# TM Product Configurator Plugin

## Overview

The TM Product Configurator is a modular WooCommerce/WordPress plugin for visual product setup. It combines option filtering, status updates, and a 3D viewer.

- **Product.js**: Handles option availability, default selection logic, and image/status updates.
- **CurrentStatus.js**: Updates the status recap (price, dimensions, spec, QR code).
- **ModelSelection.js**: Manages selected model classes and related UI state.
- **ProductRenders.js**: Runs the Three.js viewer and keeps render state in sync with selections.

## Structure & Flow

```mermaid
flowchart TD
    A[User interacts with configurator UI] --> B[Product.js]
    B -->|Dispatches colourOptionsChanged| C[ProductRenders.js]
    B --> D[CurrentStatus.js]
    D -->|Updates status recap| E[UI]
    B -->|Updates options| E
    C -->|Renders 3D model| F[3D Viewer]
    C -->|Updates QR code| G[QR Code]
    D -->|Updates QR code| G
    H[ModelSelection.js] -->|Sets model class| B
    H -->|Sets model class| D
    H -->|Sets model class| C
    subgraph UI Layer
        A
        E
        G
        F
    end
    subgraph Logic Layer
        B
        D
        H
    end
    subgraph Render Layer
        C
    end
```

## Flow Summary
1. User changes colours, base, metal, or model.
2. Product.js recalculates valid options, applies defaults, and dispatches `colourOptionsChanged`.
3. CurrentStatus.js updates price/spec/summary details.
4. ProductRenders.js updates model materials and URL/QR state.
5. ModelSelection.js keeps model class state in sync across components.