<?php
// Add your custom functions here.

// Shortcode for the course description
add_shortcode('masterstudy_course_description', function($atts) {
    // Get the course ID in the correct context
    $course_id = get_the_ID();
    
    // Verify if we are in a valid course
    if (!$course_id || get_post_type($course_id) !== 'stm-courses') {
        return 'Course not found';
    }
    
    // Attempt to get the description (adjust the meta field if necessary)
    $description = get_post_meta($course_id, 'course_short_description', true);
    
    // Fallback: use the excerpt or content if no meta field is found
    if (empty($description)) {
        $description = has_excerpt($course_id) ? get_the_excerpt($course_id) : wp_strip_all_tags(get_the_content(null, false, $course_id));
    }
    
    // Limit to 20 words
    return $description ? wp_trim_words($description, 20, '...') : 'No description available';
});

// Shortcode for the course rating stars
add_shortcode('masterstudy_course_stars', function($atts) {
    // Get the course ID
    $course_id = get_the_ID();
    
    // Verify if we are in a valid course
    if (!$course_id || get_post_type($course_id) !== 'stm-courses') {
        return 'Course not found';
    }
    
    // Get the rating (adjust the meta field if necessary)
    $rating = get_post_meta($course_id, 'rating', true); // MasterStudy typically uses 'rating'
    
    if ($rating && is_numeric($rating)) {
        $stars = min(round(floatval($rating)), 5); // Ensure it doesn't exceed 5 stars
        $output = '<div class="course-stars">';
        for ($i = 1; $i <= 5; $i++) {
            $output .= $i <= $stars ? '<span class="star filled">★</span>' : '<span class="star">☆</span>';
        }
        $output .= '</div>';
        return $output;
    }
    
    return 'No ratings available';
});

// Shortcode for the number of enrolled students
add_shortcode('masterstudy_course_enrolled', function($atts) {
    // Get the course ID
    $course_id = get_the_ID();
    
    // Verify if we are in a valid course
    if (!$course_id || get_post_type($course_id) !== 'stm-courses') {
        return 'Course not found';
    }
    
    // Get the number of students (adjust the meta field if necessary)
    $students = get_post_meta($course_id, 'current_students', true); // MasterStudy typically uses 'current_students'
    
    return $students && is_numeric($students) ? number_format_i18n($students) . ' students' : '0 students';
});


// GET INSTRUCTOR NAME
function custom_instructor_name_shortcode($atts) {
    $atts = shortcode_atts(array(
        'course_id' => get_the_ID(),
    ), $atts);

    $course_id = $atts['course_id'];

    // First, try to get the instructor from the meta field
    $instructor_id = get_post_meta($course_id, 'course_instructor', true);

    // If no instructor ID is found in meta, fall back to the course author
    if (!$instructor_id) {
        $course = get_post($course_id);
        $instructor_id = $course->post_author;
    }

    // If still no instructor ID, check if the logged-in user is an instructor (frontend builder context)
    if (!$instructor_id && is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        if (in_array('stm_lms_instructor', $user_roles) || in_array('administrator', $user_roles)) {
            $instructor_id = $current_user->ID;
        }
    }

    // Debug: Log the course ID and instructor ID
    error_log('Course ID: ' . $course_id . ' | Instructor ID: ' . $instructor_id);

    // If an instructor ID exists, fetch the instructor's name
    if ($instructor_id) {
        $instructor = get_userdata($instructor_id);
        if ($instructor) {
            return esc_html($instructor->display_name); // Should return "Ben Myhre"
        }
    }

    return 'No instructor assigned';
}
add_shortcode('instructor_name', 'custom_instructor_name_shortcode');

//GET INSTRUCTOR PROFILE PICTURE
function custom_instructor_image_url_shortcode($atts) {
    $atts = shortcode_atts(array(
        'course_id' => get_the_ID(),
    ), $atts);

    $course_id = $atts['course_id'];

    // First, try to get the instructor from the meta field
    $instructor_id = get_post_meta($course_id, 'course_instructor', true);

    // If no instructor ID is found in meta, fall back to the course author
    if (!$instructor_id) {
        $course = get_post($course_id);
        $instructor_id = $course ? $course->post_author : 0;
    }

    // If still no instructor ID, check if the logged-in user is an instructor
    if (!$instructor_id && is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        if (in_array('stm_lms_instructor', $user_roles) || in_array('administrator', $user_roles)) {
            $instructor_id = $current_user->ID;
        }
    }

    // If an instructor ID exists, get the avatar URL
    if ($instructor_id) {
        $avatar_url = get_avatar_url($instructor_id, array('size' => 150)); // Adjust size as needed
        if ($avatar_url) {
            return esc_url($avatar_url); // Returns the URL, e.g., https://arcana.pruebalucuma.site/wp-content/uploads/avatars/5/avatar.jpg
        }
    }

    // Fallback if no image is found
    return esc_url('https://arcana.pruebalucuma.site/wp-content/uploads/default-avatar.jpg'); // Replace with your default image URL
}
add_shortcode('instructor_image_url', 'custom_instructor_image_url_shortcode');
