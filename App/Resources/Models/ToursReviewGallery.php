<?php

/*
 * Copyright (C) 2016 Algie
 *
 */

namespace Tourpage\Models;

/**
 * Model Products Images
 * @author amit
 */
class ToursReviewGallery extends ApplicationModel {
    /**
     * Initializing Model Products Images
     */
    public function initialize() {
        $this->belongsTo('reviewId', '\Tourpage\Models\ToursReview', 'reviewId');
        $this->belongsTo('tourId', '\Tourpage\Models\Tours', 'tourId');
    }
}
