# WooCommerce guide

WooCommerce-specific translation, HPOS, blocks, order-language email, cart-language preservation, product adapters, and multicurrency are not implemented in 0.1.0. Products and variations can be represented as native post objects in the generic relationship schema, but this alone is not advertised as WooCommerce compatibility.

Do not deploy this release expecting translated checkout or currency behavior. That module requires public WooCommerce CRUD APIs and an HPOS/Cart/Checkout/Product Editor E2E matrix before it can be enabled.

