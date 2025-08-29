<?php
/**
 * Comments Template
 */

if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area">
    <?php if (have_comments()) : ?>
        <h2 class="comments-title">
            <?php
            $comment_count = get_comments_number();
            if ($comment_count === 1) {
                echo '1 komentarz';
            } else {
                printf('%1$s komentarzy', $comment_count);
            }
            ?>
        </h2>
        
        <ol class="comment-list">
            <?php
            wp_list_comments(array(
                'style' => 'ol',
                'short_ping' => true,
                'callback' => 'preomar_comment_callback'
            ));
            ?>
        </ol>
        
        <?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
            <nav class="comment-navigation">
                <div class="nav-links">
                    <div class="nav-previous"><?php previous_comments_link(__('Starsze komentarze', 'preomar')); ?></div>
                    <div class="nav-next"><?php next_comments_link(__('Nowsze komentarze', 'preomar')); ?></div>
                </div>
            </nav>
        <?php endif; ?>
        
    <?php endif; ?>
    
    <?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
        <p class="no-comments">Komentarze są zamknięte.</p>
    <?php endif; ?>
    
    <?php
    $comment_form = array(
        'title_reply' => __('Dodaj komentarz', 'preomar'),
        'title_reply_to' => __('Odpowiedz na komentarz %s', 'preomar'),
        'cancel_reply_link' => __('Anuluj odpowiedź', 'preomar'),
        'label_submit' => __('Wyślij komentarz', 'preomar'),
        'comment_field' => '<p class="comment-form-comment"><label for="comment">Komentarz</label><textarea id="comment" name="comment" cols="45" rows="8" required="required"></textarea></p>',
        'fields' => array(
            'author' => '<p class="comment-form-author"><label for="author">Imię <span class="required">*</span></label><input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '" size="30" required="required" /></p>',
            'email' => '<p class="comment-form-email"><label for="email">Email <span class="required">*</span></label><input id="email" name="email" type="email" value="' . esc_attr($commenter['comment_author_email']) . '" size="30" required="required" /></p>',
            'url' => '<p class="comment-form-url"><label for="url">Strona internetowa</label><input id="url" name="url" type="url" value="' . esc_attr($commenter['comment_author_url']) . '" size="30" /></p>',
        ),
        'class_submit' => 'btn btn-primary',
        'submit_field' => '<p class="form-submit">%1$s %2$s</p>',
    );
    
    comment_form($comment_form);
    ?>
</div>

<?php
/**
 * Custom comment callback
 */
function preomar_comment_callback($comment, $args, $depth) {
    $tag = ($args['style'] === 'div') ? 'div' : 'li';
    ?>
    <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class(empty($args['has_children']) ? '' : 'parent'); ?>>
        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
            <footer class="comment-meta">
                <div class="comment-author vcard">
                    <?php echo get_avatar($comment, $args['avatar_size']); ?>
                    <b class="fn"><?php comment_author_link(); ?></b>
                </div>
                
                <div class="comment-metadata">
                    <a href="<?php echo htmlspecialchars(get_comment_link($comment->comment_ID)); ?>">
                        <time datetime="<?php comment_time('c'); ?>">
                            <?php printf(__('%1$s o %2$s', 'preomar'), get_comment_date(), get_comment_time()); ?>
                        </time>
                    </a>
                    <?php edit_comment_link(__('Edytuj', 'preomar'), '<span class="edit-link">', '</span>'); ?>
                </div>
                
                <?php if ($comment->comment_approved == '0') : ?>
                    <em class="comment-awaiting-moderation">Twój komentarz oczekuje na moderację.</em>
                <?php endif; ?>
            </footer>
            
            <div class="comment-content">
                <?php comment_text(); ?>
            </div>
            
            <div class="reply">
                <?php comment_reply_link(array_merge($args, array(
                    'add_below' => 'div-comment',
                    'depth' => $depth,
                    'max_depth' => $args['max_depth']
                ))); ?>
            </div>
        </article>
    <?php
}
?>
