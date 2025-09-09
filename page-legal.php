<?php
/*
Template Name: Preo – Legal (raw content)
*/
get_header();
?>
<div class="site-container">
  <main class="site-content" style="max-width:1080px;margin:0 auto;padding:16px;">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class('page-legal'); ?>>
        <header class="entry-header">
          <h1 class="entry-title"><?php the_title(); ?></h1>
        </header>
        <div class="entry-content">
          <?php
            // Render raw page content bypassing the_content filters that maintenance plugins may hijack
            $raw = get_post_field('post_content', get_the_ID());
            echo wpautop(do_shortcode($raw));
          ?>
        </div>
      </article>
    <?php endwhile; endif; ?>
  </main>
 </div>
<?php get_footer();
// End of file
?>
