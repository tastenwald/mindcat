<article class="mindcat-card">
    <!-- Image de l'article -->
    <div class="mindcat-card-image">
        <a href="<?php the_permalink(); ?>" class="mindcat-post-thumbnail">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('medium'); ?>
            <?php endif; ?>
        </a>
    </div>

    <!-- Contenu de l'article -->
    <div class="mindcat-card-content">
        <h3 class="mindcat-card-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>
        
        <!-- Extrait de l'article -->
        <p class="mindcat-card-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>

        <!-- Lien pour lire l'article complet -->
        <a href="<?php the_permalink(); ?>" class="mindcat-read-more">Lire la suite</a>
    </div>
</article>

