<?php
/**
 * function wpbb_default_application_form_fields()
 *
 * adds the default fields for the application form
 * @param	array	$fields in the current array of fields
 * @param	int		$job_post_id is the post id of the job being applied for
 */
function wpbb_default_application_form_fields( $fields, $job_post_id ) {
	
	/* add the field (hidden) for job reference */
	$fields[ 'job_reference' ] = array(
		'name'		=> 'job_reference', // this forms the name part of the input and the meta key when the application is saved
		'type'		=> 'hidden',
		'class'		=> 'wpbb-job-reference',
		'value'		=> $_GET[ 'job_id' ],
		'label'		=> 'Job Reference', // not used for hidden fields
		'required'	=> false
	);
	
	/* add the field (hidden) for job reference */
	$fields[ 'job_post_id' ] = array(
		'name'		=> 'job_post_id',
		'type'		=> 'hidden',
		'class'		=> 'wpbb-job-postid',
		'value'		=> $job_post_id,
		'label'		=> 'Job Post ID', // not used for hidden fields
		'required'	=> false
	);
	
	/* add the field (hidden) for contact email */
	$fields[ 'contact_email' ] = array(
		'name'		=> 'contact_email',
		'type'		=> 'hidden',
		'class'		=> 'wpbb-contact-email',
		'value'		=> get_post_meta( $job_post_id, '_wpbb_job_contact_email', true ),
		'label'		=> 'Contact Email', // not used for hidden fields
		'required'	=> false
	);
	
	/* add the field (hidden) for tracking email */
	$fields[ 'tracking_email' ] = array(
		'name'		=> 'tracking_email',
		'type'		=> 'hidden',
		'class'		=> 'wpbb-tracking-email',
		'value'		=> get_post_meta( $job_post_id, '_wpbb_job_broadbean_application_email', true ),
		'label'		=> 'Tracking Email', // not used for hidden fields
		'required'	=> false
	);
	
	/* add the field (hidden) for name */
	$fields[ 'applicant_name' ] = array(
		'name'		=> 'applicant_name',
		'type'		=> 'text',
		'class'		=> 'wpbb-applicant-name',
		'value'		=> '',
		'label'		=> 'Name', // not used for hidden fields
		'desc'		=> 'Please enter your full name',
		'required'	=> true
	);
	
	/* add the field (hidden) for name */
	$fields[ 'applicant_email' ] = array(
		'name'		=> 'applicant_email',
		'type'		=> 'email',
		'class'		=> 'wpbb-applicant-email',
		'value'		=> '',
		'label'		=> 'Email', // not used for hidden fields
		'desc'		=> 'Please enter a valid email address.',
		'required'	=> true
	);
	
	/**
	 * file upload fields are not saved as post meta
	 * they are upload as an attachment to the application post
	 * the files are also attached to the notification email
	 */
	$fields[ 'cv' ] = array(
		'name'		=> 'applicant_cv',
		'type'		=> 'file',
		'class'		=> 'wpbb-applicant-cv',
		'value'		=> '',
		'label'		=> 'CV Upload', // not used for hidden fields
		'desc'		=> 'Upload your CV in PDF format.',
		'required'	=> false
	);
	
	return $fields;
	
}

add_filter( 'wpbb_application_form_fields', 'wpbb_default_application_form_fields', 10, 2 );

/**
 * function wpbb_application_form_shortcode()
 * creates the application form shortcode which outputs the application form
 * @param (string) $content is the current post content
 * @return (string) $content is the new content with the form added after
 */
function wpbb_get_application_form_fields( $job_post_id ) {
				
	/**
	 * form fields are added using a filter
	 * to best see how this works, see how the default fields are added
	 * default fields are added in the function wpbb_default_application_form_fields()
	 */
	$form_fields = apply_filters(
		'wpbb_application_form_fields',
		array(),
		$job_post_id
	);
	
	/* check we have fields to output */
	if( ! empty( $form_fields ) ) {
		
		/* start a counter */
		$i = 1;
		
		/* loop through the form fields array */
		foreach( $form_fields as $form_field ) {
			
			/* is this a required field */
			if( $form_field[ 'required' ] == true ) {
				$required = ' required';
			} else {
				$required = '';
			}
			
			/* build the css class for all fields */
			$class = 'wpbb-field wpbb-field-' . $form_field[ 'type' ];
			
			/* if this is not a hidden field */
			if( $form_field[ 'type' ] != 'hidden' ) {
				
				/* add the counter to the class */
				$class .= ' wpbb-field-' . $i;
				
			} // end if hidden field
			
			?>
			<div class="<?php echo esc_attr( $class ); ?>">
			<?php
				
			/* if this is not a hiiden field - output the field label */
			if( $form_field[ 'type' ] != 'hidden' && $form_field[ 'type' ] != 'file' ) {
				
				?>
				<label for="wpbb_meta_<?php echo esc_attr( $form_field[ 'name' ] ); ?>"><?php echo esc_html( $form_field[ 'label' ] ); ?></label>
				<?php
				
			}
			
			/* switch depending on field type */
			switch( $form_field[ 'type' ] ) {
				
				/* if the setting is a textarea input */
				case 'hidden' :
				
					?>
					
					<input type="hidden" name="wpbb_meta_<?php echo esc_attr( $form_field[ 'name' ] ); ?>" value="<?php echo esc_attr( $form_field[ 'value' ] ); ?>" />
					
					<?php
				
					/* break out of the switch statement */
					break;
				
				/* if the setting type is an email field */
				case 'email' :
				
					?>
				
					<input type="email" class="<?php echo esc_attr( $form_field[ 'class' ] ); ?>" name="wpbb_meta_<?php echo esc_attr( $form_field[ 'name' ] ); ?>" value="<?php echo esc_attr( $form_field[ 'value' ] ); ?>"<?php echo esc_attr( $required ); ?> />
					
					<?php
						
					/* break out of the switch statement */
					break;
					
				/* if this is a file upload field */
				case 'file' :
				
					?>
					<label for="wpbb_file_<?php echo esc_attr( $form_field[ 'name' ] ); ?>"><?php echo esc_html( $form_field[ 'label' ] ); ?></label>
					<input type="file" class="<?php echo esc_attr( $form_field[ 'class' ] ); ?>" name="wpbb_file_<?php echo esc_attr( $form_field[ 'name' ] ); ?>" value=""<?php echo esc_attr( $required ); ?> />
					
					<?php
					
					/* break out of the switch statement */
					break;
				
				/* treat all other types as a text input */
				default :
				
					?>
				
					<input type="text" class="<?php echo esc_attr( $form_field[ 'class' ] ); ?>" name="wpbb_meta_<?php echo esc_attr( $form_field[ 'name' ] ); ?>" value="<?php echo esc_attr( $form_field[ 'value' ] ); ?>"<?php echo esc_attr( $required ); ?> />
					
					<?php
				
			} // end switch
			
			/* check if we have a description to output */
			if( ! empty( $form_field[ 'desc' ] ) ) {
				
				?>
				<p class="<?php echo apply_filters( 'wpbb_input_description', 'wpbb-input-description', $form_field[ 'name' ] ); ?>"><?php echo esc_html( $form_field[ 'desc' ] ); ?></p>
				<?php
				
			} // end if have description
			
			?>
			</div><!-- // input wrapper -->
			<?php
			
			/* increment the counter for non hidden fields */
			if( $form_field[ 'type' ] != 'hidden' ) {
				$i++;
			}
			
		} // end loop through form fields
					
	} // end if have form fields to output
				
}

/**
 * function wpbb_application_processing()
 * process the application form submitted creating an application post
 * @param (int) $job_ref is the job reference for the job being applied for
 */
function wpbb_application_processing() {
	
	/* if this is the admin then bail early */
	if( is_admin() ) {
		return;
	}
	
	/* check whether the form has been posted */
	if( ! isset( $_POST[ 'submit_application' ] ) ) {
		return;	
	}
	
	/* setup some arrays to store meta related and file related fields */
	$wpbb_meta_save		= array();
	$wpbb_attach_save	= array();
	
	/* get all the posted data */
	$posted = $_POST;
	
	/* build an array of meta values to save */
	foreach( $_POST as $key => $value ) {
		
		if( substr( $key, 0, 10 ) == 'wpbb_meta_' ) {
			
			/* remove the wpbb_meta_ part of the key */
			$key = substr( $key, 10 );
			
			/* add this to the meta array */
			$wpbb_meta_save[ $key ] = $value;
			
		}
		
	}
	
	/* build an array of meta values to save */
	foreach( $_FILES as $key => $value ) {
		
		if( substr( $key, 0, 10 ) == 'wpbb_file_' ) {
			
			/* remove the wpbb_meta_ part of the key */
			$key = substr( $key, 10 );
			
			/* add this to the meta array */
			$wpbb_attach_save[ $key ] = $value;
			
		}
		
	}
	
	/* insert the application post */
	$wpbb_application_id = wp_insert_post(
		array(
			'post_type' => 'wpbb_application',
			'post_title' => wp_strip_all_tags( $wpbb_meta_save[ 'applicant_name' ] ),
			'post_status' => 'publish'
		)
	);
	
	/* store message on success/failure in this array */
	global $wpbb_application_form_messages;
	$wpbb_application_form_messages = array();
	
	/* check that the wp_handle_upload function is loaded */		
	if ( ! function_exists( 'wp_handle_upload' ) )
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	
	/* check we have files to attach */
	if( ! empty( $wpbb_attach_save ) ) {
		
		/* attachments array */
		$attachments = array();
		
		/* generate array of allowed mime types */
		$wpbb_allowed_mime_types = apply_filters(
			'wpbb_application_allowed_file_types',
			array(
				'application/pdf',
			)
		);
		
		/* get the wp upload directory */
		$wpbb_wp_upload_dir = wp_upload_dir();
		
		/* loop through each of the files to attach */
		foreach( $wpbb_attach_save as $file ) {
			
			/* upload the file to wp uploads dir */
			$moved_file = wp_handle_upload(
				$file,
				array( 'test_form' => false )
			);
			
			/* get file type of the uploaded file */
			$filetype = wp_check_filetype( $moved_file[ 'url' ], null );
			
			/* check uploaded file is in allowed mime types array */
			if( ! in_array( $filetype[ 'type' ], $wpbb_allowed_mime_types ) ) {
			
				/* upload file not allowed - add to messages */
				$wpbb_application_form_messages[] = '<p class="message error">Error: CV is not an allowed file type.</p>';
			
			} else {
				
				/* setup the attachment data */
				$wpbb_attachment = array(
				     'post_mime_type' => $filetype[ 'type' ],
				     'post_title' => preg_replace('/\.[^.]+$/', '', $file[ 'name' ] ),
				     'post_content' => '',
				     'guid' => $wpbb_wp_upload_dir[ 'url' ] . '/' . sanitize_file_name( $file[ 'name' ] ),
				     'post_status' => 'inherit'
				);
				
				/* add the attachment from the uploaded file */
				$wpbb_attach_id = wp_insert_attachment( $wpbb_attachment, $moved_file[ 'file' ], $wpbb_application_id );
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				$wpbb_attach_data = wp_generate_attachment_metadata( $wpbb_attach_id, WP_CONTENT_DIR . '/uploads' . $wpbb_wp_upload_dir[ 'subdir' ] . '/' . $file[ 'name' ] );
				wp_update_attachment_metadata( $wpbb_attach_id, $wpbb_attach_data );
				
				/* set attachments - the cv */
				$attachments[] = $moved_file[ 'file' ];
				
			}
			
		}
	
	}
	
	/* check the application post has been added */
	if( $wpbb_application_id != 0 ) {
		
		/* loop through each posted field to save as meta data */
		foreach( $wpbb_meta_save as $cf_key => $cf_value ) {
			
			/* add the post meta */
			add_post_meta( $wpbb_application_id, '_wpbb_' . $cf_key, sanitize_text_field( $cf_value ), true );
			
		}
						
		/* set an output message */
		$wpbb_application_form_messages[] = '<p class="message success">Thank you. You application has been received.</p>';
		
	} // end check application post added
	
	/* send the email */
	wpbb_send_application_email(
		$attachments,
		$wpbb_meta_save,
		$wpbb_application_id
	);
	
}

add_action( 'wp', 'wpbb_application_processing', 10 );