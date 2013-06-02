<?php
/**
 * Search Form Template
 *
 * This template is a customised search form.
 *
 * @package WooFramework
 * @subpackage Template
 */
?>
<div class="search_main fix">
    <form method="get" class="searchform" action="<?php echo home_url( '/' ); ?>" >
        <input type="text" class="field s" name="s" placeholder="<?php esc_attr_e( 'Search...', 'woothemes' ); ?>" />
        <input type="submit" class="search-submit" name="submit" alt="Submit" value="Search" />
    </form>    
</div><!--/.search_main-->