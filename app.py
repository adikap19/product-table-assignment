"""
Main Flask application.
Renders the product table page with server-side pagination and search.
"""

from flask import Flask, render_template, request
from services.product_service import get_products
import math

app = Flask(__name__)

ITEMS_PER_PAGE = 10

@app.route("/")
def index():
    # Renders the main page with products, pagination, and search results.
    try:
        page = int(request.args.get("page", 1))
        if page < 1:
            page = 1
    except (ValueError, TypeError):
        page = 1

    search_query = request.args.get("q", "").strip()
    skip = (page - 1) * ITEMS_PER_PAGE

    result = get_products(skip=skip, limit=ITEMS_PER_PAGE, search_query=search_query or None)

    total_items = result.get("total", 0)
    total_pages = max(1, math.ceil(total_items / ITEMS_PER_PAGE))

    if page > total_pages:
        page = total_pages

    return render_template(
        "index.html",
        products=result.get("products", []),
        current_page=page,
        total_pages=total_pages,
        search_query=search_query,
        error=result.get("error"),
    )


if __name__ == "__main__":
    app.run(debug=True)