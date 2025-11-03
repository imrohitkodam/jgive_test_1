<?php
/**
 * @package     JGive
 * @subpackage  com_jgive
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;

// Default image path dynamically
$defaultImagePath = JUri::root() . 'media/com_jgive/images/default_avatar.png';

// Check if the campaign has beneficiary stories
if (!empty($this->item['campaign']->beneficiaryStories)) : ?>

    <!-- Testimonials Carousel -->
    <div id="testimonialsCarousel" class="carousel slide testimonials-container mb-5" data-bs-ride="carousel">
        <div class="carousel-inner shadow">
            <?php 
            // Loop through each beneficiary story
            $stories = $this->item['campaign']->beneficiaryStories;
            $totalStories = count($stories);
            $storyPairs = array_chunk($stories, 2);
            foreach ($storyPairs as $index => $pair) : ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="row justify-content-center">
                        <?php foreach ($pair as $story) : ?>
                            <div class="col-md-5 story">
                                <div class="card bg text-light p-0 mx-auto border-0 beneficiary-stories-card collapsed">
                                    <div class="card-body row align-items-start g-0">
                                        <!-- Left Section: Image and Details -->
                                        <div class="col-md-8 d-flex align-items-center text-center text-md-start mb-0 mb-md-0">
                                            <!-- Display beneficiary image or a default image if none exists -->
                                            <img src="<?php echo !empty($story['image']) ? htmlspecialchars($story['image']) : $defaultImagePath; ?>" 
                                                class="img-fluid mb-3 mt-3 rounded-1"
                                                alt="Profile Image">

                                            <!-- Display beneficiary name -->
                                            <h5 class="card-title text-primary mb-1">
                                                <?php echo htmlspecialchars($story['beneficiary_name']); ?>
                                            </h5>

                                            <?php 
                                            // Check and display the beneficiary's position if available
                                            if (!empty($story['beneficiary_position'])) : ?>
                                                <p class="card-text text-dark mb-2 fw-bold">
                                                    <?php echo htmlspecialchars($story['beneficiary_position']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Right Section: Story Description -->
                                        <div class="col-md-0">
                                            <!-- Display the beneficiary story description -->
                                            <p class="text-muted mb-0 position-relative story-description text-overflow-ellipsis overflow-hidden" data-max-length="170">
                                                <i class="fa fa-quote-left fs-3 text-secondary me-3 opacity-25"></i>
                                                    <span class="story-content">
                                                        <?php echo htmlspecialchars($story['story_description']); ?>
                                                    </span>
                                                <i class="fa fa-quote-right fs-3 text-secondary position-absolute bottom-2 end-2 opacity-25 ms-2"></i>
                                            </p>
                                            <!-- Show More/Less Button -->
                                            <button class="btn btn-link p-0 text-primary show-more-less d-none"><?php echo Text::_("COM_JGIVE_SHOW_MORE");?></button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Carousel Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bg-primary rounded-circle position-absolute start-0 top-20 translate-middle-y" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>

            <button class="carousel-control-next" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bg-primary rounded-circle position-absolute top-20 end-0 translate-middle-y" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
    </div>

<?php else : ?>
    <!-- Display message if no beneficiary stories are available -->
    <p>No beneficiary stories available for this campaign.</p>
<?php endif; ?>

<!-- Add jQuery Script -->
<script>
    jQuery(document).ready(function ($) {
        // Get language constants from PHP to dynamically update text
        const SHOW_MORE_TEXT = "<?php echo Text::_('COM_JGIVE_SHOW_MORE'); ?>";
        const SHOW_LESS_TEXT = "<?php echo Text::_('COM_JGIVE_SHOW_LESS'); ?>";

        // Iterate over each story description element
        $('.story-description').each(function () {
            const $this = $(this);
            const $content = $this.find('.story-content');
            const $secondIcon = $this.find('i.fa-quote-right');
            const card = $this.closest('.beneficiary-stories-card');
            const maxLength = $this.data('max-length');
            const fullText = $this.text().trim();

            // Check if the description text exceeds the maximum length
            if (fullText.length > maxLength) {
                // Shorten the text to the maximum length and add ellipsis
                const shortText = fullText.substring(0, maxLength) + '...';
                $secondIcon.hide();

                // Set initial state with short text and collapsed card
                $content.text(shortText);
                card.addClass('collapsed'); // Add collapsed class for initial collapsed state

                // Make the "Show More/Less" button visible
                $this.siblings('.show-more-less').removeClass('d-none').show();

                // Add click event listener to toggle the expanded/collapsed state
                $this.siblings('.show-more-less').click(function () {
                    // If the card is collapsed, expand it
                    if (card.hasClass('collapsed')) {
                        card.removeClass('collapsed').addClass('expanded'); // Expand the card
                        $content.text(fullText);
                        $secondIcon.show();
                        $(this).text(SHOW_LESS_TEXT);
                    } else {
                        // If the card is expanded, collapse it
                        card.removeClass('expanded').addClass('collapsed'); // Collapse the card
                        $content.text(shortText);
                        $secondIcon.hide();
                        $(this).text(SHOW_MORE_TEXT);
                    }
                });
            }
        });
    });
</script>
