<div class="post-container">

	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
		<?php if ( has_post_thumbnail() ) : ?>
		
			<a class="featured-media" title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>">	
				
				<?php the_post_thumbnail('post-thumb'); ?>
				
			</a> <!-- /featured-media -->
				
		<?php endif; ?>
		
		<?php 
			$post_title = get_the_title();
			if ( !empty( $post_title ) ) : 
		?>
					
			<div class="post-header">
				
			    <h2 class="post-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
			    	    
			</div> <!-- /post-header -->
		
		<?php endif; ?>
		
		<div class="post-excerpt">
		
			<?php the_excerpt(); ?>
		
		</div>
		
		<?php if ( empty( $post_title ) ) : ?>
			    
		    <div class="posts-meta">
		    
		    	<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_time(get_option('date_format')); ?></a>
		    	
	    	</div>
	    
	    <?php endif; ?>
<!--//XTEC ************ AFEGIT - Added tags and categories information
//2016.02.04 @sarjona -->
		<?php
			$output = '';
			$categories = get_the_category();
			foreach($categories as $category) {
                $output .= '<a href="'.get_category_link( $category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $category->name ) ) . '">'.$category->cat_name.'</a>'.", ";
            }
            if (!empty($output)) {
            	echo '<span class="entry-categories wp-color-result">';
            	echo trim($output, ", ");
            	echo '</span>';
            }
        ?>
		<?php $tag = get_the_tags(); if (!$tag) { } else { ?><?php $string_tags=trim(get_the_tag_list("",", "),","); echo '<span class="entry-tags">'; echo $string_tags; echo '</span>'; ?><?php } ?>
	</span>
<!--//************ FI -->
	</div> <!-- /post -->

</div> <!-- /post-container -->