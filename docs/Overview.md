# TM Product Configurator Plugin

## Overview

The TM Product Configurator is a modular JavaScript plugin for WooCommerce/WordPress that enables users to visually configure products (e.g., tables) with real-time 3D previews, dynamic option filtering, and live status recaps. It is composed of several classes, each responsible for a distinct aspect of the configurator:

- **ColourOptions.js**: Manages available colour, base, and metal options, updates UI, and dispatches events when selections change.
- **CurrentStatus.js**: Updates the status recap (price, dimensions, spec, QR code) based on current selections.
- **ModelSelection.js**: Handles model selection, syncing the model class across the UI and logic.
- **ProductRenders.js**: Handles 3D rendering, updates the model based on selections, and manages the QR code for sharing.

## Structure & Flow

```mermaid
flowchart TD
    A[User interacts with configurator UI] --> B[ColourOptions.js]
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
1. **User interacts with the configurator UI** (selects colours, base, metal, or model).
2. **ColourOptions.js** updates available options, enforces defaults, and dispatches a `colourOptionsChanged` event.
3. **CurrentStatus.js** updates the status recap (price, dimensions, spec, QR code) in real time.
4. **ProductRenders.js** listens for option changes, updates the 3D model, and synchronizes the QR code.
5. **ModelSelection.js** manages the selected model and ensures all modules are in sync.