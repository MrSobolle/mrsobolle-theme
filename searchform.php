<?php defined( 'ABSPATH' ) || exit; ?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( \MrSobolle\Data\Site_Data::home_url() ); ?>">
<label><span class="screen-reader-text"><?php esc_html_e( 'Search the site', 'mrsobolle' ); ?></span>
<input type="search" name="s" value="<?php echo esc_attr( get_search_query( false ) ); ?>" placeholder="<?php esc_attr_e( 'Search the site', 'mrsobolle' ); ?>" required maxlength="150"></label>
<?php if ( function_exists( 'pll_current_language' ) ) : ?><input type="hidden" name="lang" value="<?php echo esc_attr( pll_current_language( 'slug' ) ); ?>"><?php endif; ?>
<button class="button" type="submit"><?php esc_html_e( 'Search', 'mrsobolle' ); ?></button>
</form>
