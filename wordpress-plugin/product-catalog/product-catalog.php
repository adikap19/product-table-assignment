<?php
/**
 * Plugin Name: Product Catalog Compare
 */

defined('ABSPATH') || exit;

define('PCC_ITEMS_PER_PAGE', 10);
define('PCC_PAGE_SLUG', 'compare-assignment');

/**
 * Auto-creates the "Compare Assignment" page on plugin activation.
 */
function pcc_create_page_on_activation() {
    if (!get_page_by_path(PCC_PAGE_SLUG)) {
        wp_insert_post([
            'post_title'   => 'Compare Assignment',
            'post_name'    => PCC_PAGE_SLUG,
            'post_content' => '[product_catalog]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }
}
register_activation_hook(__FILE__, 'pcc_create_page_on_activation');

/**
 * Fetches products from DummyJSON with pagination and optional search.
 */
function pcc_get_products($skip, $limit, $search_query = null) {
    $base_url = $search_query
        ? 'https://dummyjson.com/products/search'
        : 'https://dummyjson.com/products';

    $params = ['limit' => $limit, 'skip' => $skip];
    if ($search_query) {
        $params['q'] = $search_query;
    }

    $url = $base_url . '?' . http_build_query($params);
    $response = wp_remote_get($url, ['timeout' => 10]);

    if (is_wp_error($response)) {
        return ['products' => [], 'total' => 0, 'error' => $response->get_error_message()];
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    return [
        'products' => $body['products'] ?? [],
        'total'    => $body['total'] ?? 0,
    ];
}

/**
 * Renders the product table, search bar, and pagination.
 * Registered via the [product_catalog] shortcode.
 */
function pcc_render_catalog() {
    $page = isset($_GET['pcc_page']) ? max(1, intval($_GET['pcc_page'])) : 1;
    $search_query = isset($_GET['pcc_q']) ? sanitize_text_field($_GET['pcc_q']) : '';
    $skip = ($page - 1) * PCC_ITEMS_PER_PAGE;

    $result = pcc_get_products($skip, PCC_ITEMS_PER_PAGE, $search_query ?: null);
    $products = $result['products'];
    $total = $result['total'];
    $total_pages = max(1, ceil($total / PCC_ITEMS_PER_PAGE));

    if ($page > $total_pages) {
        $page = $total_pages;
    }

    $base_url = strtok($_SERVER['REQUEST_URI'], '?');

    ob_start();
    ?>
    <div class="pcc-container">
        <form method="GET" action="<?php echo esc_url($base_url); ?>" class="pcc-search-form">
            <input type="text" name="pcc_q" placeholder="Search products..."
                   value="<?php echo esc_attr($search_query); ?>" class="pcc-search-input">
            <button type="submit" class="pcc-btn">Search</button>
            <?php if ($search_query): ?>
                <a href="<?php echo esc_url($base_url); ?>" class="pcc-btn pcc-btn-clear">Clear</a>
            <?php endif; ?>
        </form>

        <?php if (!empty($result['error'])): ?>
            <div class="pcc-error">Unable to load products. Please try again later.</div>
        <?php endif; ?>

        <table class="pcc-table">
            <thead>
                <tr>
                    <th>Thumbnail</th><th>Title</th><th>Description</th>
                    <th>Price</th><th>Rating</th><th>Stock</th>
                    <th>Brand</th><th>Category</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="8" class="pcc-no-results">No products found.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><img src="<?php echo esc_url($product['thumbnail'] ?? ''); ?>" class="pcc-thumbnail" alt=""></td>
                            <td><?php echo esc_html($product['title'] ?? '—'); ?></td>
                            <td class="pcc-description"><?php echo esc_html($product['description'] ?? ''); ?></td>
                            <td>$<?php echo esc_html(number_format($product['price'] ?? 0, 2)); ?></td>
                            <td><?php echo esc_html($product['rating'] ?? 0); ?></td>
                            <td><?php echo esc_html($product['stock'] ?? 0); ?></td>
                            <td><?php echo esc_html($product['brand'] ?? '—'); ?></td>
                            <td><?php echo esc_html($product['category'] ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pcc-pagination">
            <?php if ($page > 1): ?>
                <a class="pcc-btn" href="<?php echo esc_url(add_query_arg(['pcc_page' => $page - 1, 'pcc_q' => $search_query], $base_url)); ?>">‹ Previous</a>
            <?php endif; ?>
            <span>Page <?php echo esc_html($page); ?> of <?php echo esc_html($total_pages); ?></span>
            <?php if ($page < $total_pages): ?>
                <a class="pcc-btn" href="<?php echo esc_url(add_query_arg(['pcc_page' => $page + 1, 'pcc_q' => $search_query], $base_url)); ?>">Next ›</a>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('product_catalog', 'pcc_render_catalog');

/**
 * Enqueues minimal styling for the catalog.
 */
function pcc_enqueue_styles() {
    wp_register_style('pcc-style', false);
    wp_enqueue_style('pcc-style');
    wp_add_inline_style('pcc-style', '
        .pcc-container { max-width: 1100px; margin: 0 auto; }
        .pcc-search-form { display: flex; gap: 10px; margin-bottom: 16px; }
        .pcc-search-input { padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; width: 250px; }
        .pcc-btn { padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; text-decoration: none; cursor: pointer; }
        .pcc-btn-clear { background: #6b7280; }
        .pcc-table { width: 100%; border-collapse: collapse; background: #fff; }
        .pcc-table th, .pcc-table td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; font-size: 14px; }
        .pcc-table th { background: #1a1a2e; color: #fff; }
        .pcc-thumbnail { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
        .pcc-description { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .pcc-pagination { display: flex; gap: 16px; align-items: center; justify-content: center; margin-top: 20px; }
        .pcc-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        .pcc-no-results { text-align: center; padding: 16px; color: #6b7280; }
    ');
}
add_action('wp_enqueue_scripts', 'pcc_enqueue_styles');