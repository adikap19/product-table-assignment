# Product Catalog - Full Stack Assignment

A Flask web application that displays products from the DummyJSON API
in a searchable, paginated table with an interactive image gallery.

## Features

- Server-side product table with pagination and a page-jump dropdown
- Server-side search via DummyJSON's search endpoint
- Interactive "Gallery" button showing up to 3 product images per row
- Graceful error handling for failed API requests

## Installation

1. Clone the repository:
```bash
   git clone https://github.com/adikap19/product-table-assignment.git
   cd product-table-assignment
```

2. Create and activate a virtual environment:
```bash
   python -m venv venv

   # Windows
   venv\Scripts\activate

   # Mac/Linux
   source venv/bin/activate
```

3. Install dependencies:
```bash
   pip install -r requirements.txt
```

4. Run the application:
```bash
   python app.py
```

5. Open your browser at `http://127.0.0.1:5000`

## How It Works

A Flask app fetches products from the DummyJSON API and renders them
server-side using Jinja2. Search and pagination are handled via GET
parameters (`q`, `page`) - no client-side API calls. The only JavaScript
is `gallery.js`, which toggles a hidden row showing up to 3 product images.

## Assumptions & Design Decisions

- 10 products per page.
- Search uses DummyJSON's `/products/search` endpoint.
- A page-jump dropdown was added alongside Previous/Next.
- API failures are handled gracefully with an error message instead of a crash.
- No frontend frameworks - vanilla HTML/CSS/JS with Jinja2.

## Bonus: WordPress Plugin

A standalone WordPress plugin is included in `wordpress-plugin/product-catalog/`.

### Installation

1. Copy the `product-catalog` folder into `wp-content/plugins/`.
2. Activate the plugin from the WordPress Admin → Plugins page.
3. On activation, a page titled **"Compare Assignment"** is created
   automatically with the product catalog displayed via shortcode.

### How It Works

The `[product_catalog]` shortcode renders a searchable, paginated product
table fetched server-side from the DummyJSON API. Search and pagination use
GET parameters (`pcc_q`, `pcc_page`), with all logic handled in PHP.