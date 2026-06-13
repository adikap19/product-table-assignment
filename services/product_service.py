"""
Service layer responsible for all communication with the DummyJSON API.
Handles fetching, pagination, and search.
"""

import requests

BASE_URL = "https://dummyjson.com/products"
SEARCH_URL = "https://dummyjson.com/products/search"

def _extract_fields(product: dict) -> dict:
    return {
        "id": product.get("id"),
        "title": product.get("title", "N/A"),
        "description": product.get("description", ""),
        "price": product.get("price", 0),
        "rating": product.get("rating", 0),
        "stock": product.get("stock", 0),
        "brand": product.get("brand", "—"),
        "category": product.get("category", "N/A"),
        "thumbnail": product.get("thumbnail", ""),
        "images": product.get("images", [])[:3],
    }


def get_products(skip: int = 0, limit: int = 10, search_query: str | None = None) -> dict:
    # Fetches products from DummyJSON, with optional search and pagination. 
    try:
        params = {"limit": limit, "skip": skip}

        if search_query:
            params["q"] = search_query
            url = SEARCH_URL
        else:
            url = BASE_URL

        response = requests.get(url, params=params, timeout=10)
        response.raise_for_status()
        data = response.json()

        products = [_extract_fields(p) for p in data.get("products", [])]

        return {
            "products": products,
            "total": data.get("total", 0),
            "skip": data.get("skip", skip),
            "limit": data.get("limit", limit),
        }

    except requests.exceptions.RequestException as e:
        # Returns empty result instead of crashing
        print(f"Error fetching products: {e}")
        return {"products": [], "total": 0, "skip": skip, "limit": limit, "error": str(e)}