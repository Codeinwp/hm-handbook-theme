<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package hm-handbook
 *
 */

namespace HM_Handbook;

?>

			<div class="site-footer Footer">

				<div class="footer-content">
					<?php dynamic_sidebar( 'footer-content' ); ?>
				</div>

			</div>

		</main>

	</div>

	<?php wp_footer(); ?>

</body>
</html>
