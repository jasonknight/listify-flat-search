<?php 
   $d = \ListifyFlatSearch\get_listing_data($p);
?>
<div class="row listing-entry-row">
    <div class="col-xs-12 col-md-3 col-lg-2">
        <div class="listing-image-center">
            <?php echo $d['image']; ?> 
        </div>
    </div> <!-- End Left Column -->
    <div class="col-xs-12 col-md-9 col-lg-10">
        <div class="row">
            <div class="col-xs-5 col-md-5 col-lg-5 listing-title" >
                <div class="row">
                    <div class="col-xs-12 col-md-12 col-lg-12 listing-title" >
                        <div class="listing-name">
                        <a href="<?php echo $d['permalink']; ?>" target="_blank">
                            <?php echo $d['title']; ?>
                        </a></div>
                        <?php echo $d['location']; ?>
                    </div>
                </div>
                <div class="row rating-row">
                    <div class="new-rating col-xs-5 col-md-5 col-lg-5">
                        <?php echo $d['rating']; ?> 
                    </div>
                    <div class="ref-count col-xs-6 col-md-6 col-lg-6">
                        <?php echo $d['ref_count']; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-md-12 col-lg-12 listing-business-type">
                        <h4>Video Production</h4>
                        <div class="top-ten-roles-terms">
                            <p class="term"><?php echo $d['business_type']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-7 col-md-7 col-lg-7">
                <span class="primary-roles-title">Primary Roles</span>
                <div class="top-ten-roles-terms">
                    <?php echo $d['roles']; ?>
                </div>
            </div>
        </div> <!-- End Title Row -->
        <div class="row row-with-top-border">
            <div class="col-xs-12 col-md-12 col-lg-12">
                <p class="comment-text">
                <?php if ( !empty($d['what_demeanor']) ): ?>
                    <?php echo $d['what_demeanor']; ?>.
                <?php endif; ?>
                <?php if ( !empty($d['pace_detail']) ): ?>
                    <?php echo $d['pace_detail']; ?>.
                <?php endif; ?>
                <?php echo $d['text']; ?></p>
            </div>
        </div> <!-- End Comment Row -->
    </div> <!-- End Right Column -->
</div> <!-- End Main Row -->

