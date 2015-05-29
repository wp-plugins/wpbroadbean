<?php
/**
 * function wpbb_application_form
 * renders the application form on the page selected for apply in settings
 * @param (string) $content is the current post content
 * @return (string) $content is the new content with the form added after
 */
function wpbb_application_form( $content ) {
	
	/* get the apply page from the settings */
	$apply_pageid = get_option( 'wpbb_apply_page_id' );
	
	/* check this is the apply page */
	if( ! is_page( $apply_pageid ) )
		return $content;
		
	/* check we have a job id passed */
	if( ! empty( $_GET[ 'job_id' ] ) ) {
		
		/* get the post for this job reference */
		$job_post = wpbb_get_job_by_reference( $_GET[ 'job_id' ] );

		/* check if we have a job post for this reference */
		if( $job_post == false ) {
			
			/* create an error message as no job has this reference */
			return '<p class="message error">Error: No job exists with this job reference.</p>' . $content;
			
		}
		
		/* get the contact email and broadbean tracking email for this job */
		$contact_email = get_post_meta( $job_post, '_wpbb_job_contact_email', true );
		$bb_email = get_post_meta( $job_post, '_wpbb_job_broadbean_application_email', true );
		
		/* check whether the form has already been posted */
		if( ! isset( $_POST[ 'wpbb_submit' ] ) ) {
			
			/* start the form */
			$form = '<div class="wpbb-form-wrapper"><form enctype="multipart/form-data" id="wpbb-application-form" method="post" action="">';
			
			/* add a hidden input field for the job ref, contact email and broadbean email */
			$form .= '<input class="wpbb-input" type="hidden" name="wpbb_job_reference" id="wpbb-job-reference" value="' . esc_attr( $_GET[ 'job_id' ] ) . '" />';
			
			$form .= '<input class="wpbb-input" type="hidden" name="wpbb_contact_email" id="wpbb-contact-email" value="' . esc_attr( $contact_email ) . '" /><input class="wpbb-input" type="hidden" name="wpbb_broadbean_application_email" id="wpbb-broadbean-application-email" value="' . esc_attr( $bb_email ) . '" />';
			
			/* add inputs for name and email address */
			$form .= '<div class="wpbb-input"><label for="wpbb_name" class="require">Name</label><input class="wpbb-input" type="text" name="wpbb_name" id="wpbb-name" value="" tabindex="3" required><label class="error" for="wpbb_name">Please enter your name.</label></div>';
			
			$form .= '<div class="wpbb-input"><label for="wpbb_email" class="require">Email</label><input class="wpbb-input" type="email" name="wpbb_email" id="wpbb-email" value="" tabindex="4" required><p class="wpbb_description">Please enter a valid email address as this will be used to contact you.</p></div>';
			
			/* add the upload input field for the cv */
			$form .= '<div class="wpbb-input"><label for="wpbb_file">Attach a CV</label><input type="file" name="wpbb_upload" /><p class="wpbb_description">Please attach your CV in PDF format.</p></div>';
			
			/* add the submit button */
			$form .= '<div class="wpbb_submit"><input type="submit" value="Submit" name="wpbb_submit"></div>';
			
			/* end the form */
			$form .= '</form></div>';
		
		/* form has been posted */	
		} else {
			
			/**
			 * TODO get the message from the form processing function as to whether the processing went through or not
			 */
			
			/* set a string to store all messages in */
			$wpbb_message_string = '';
			
			/* loop through each message adding to string */
			global $wpbb_messages;
			foreach( $wpbb_messages as $message ) {
				$wpbb_message_string .= $message . '<br />';
			}
			
		}
	
	/* no job ref was passed in the query string */	
	} else {
		
		/* set an output message rather than the form */
		$form = '<p class="message error">Error: No job reference detected!</p>';
		
	}
	
	/* make the form markup filterable */
	$form = apply_filters(
		'wpbb_application_form_html',
		$form,
		$_GET[ 'job_id' ]
	);
	
	/* add message to the form content */
	$form = $form . $wpbb_message_string;
	
	/* check we have something in our form variable */
	if( empty( $form ) )
		return $content;
	
	/* return the form with content */
	return $content . $form;
	
}

add_filter( 'the_content', 'wpbb_application_form' );

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
	
	/* check whether the form has already been posted */
	if( isset( $_POST[ 'wpbb_submit' ] ) ) {
		
		/* get the post for this job reference */
		$job_post = wpbb_get_job_by_reference( $_GET[ 'job_id' ] );
	
		/* store message on success/failure in this array */
		global $wpbb_messages;
		$wpbb_messages = array();
		
		/* check that the wp_handle_upload function is loaded */		
		if ( ! function_exists( 'wp_handle_upload' ) )
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		
		/* get the uploaded file information */
		$wpbb_uploaded_file = $_FILES[ 'wpbb_upload' ];
		
		/* check we have a file to upload */
		if( $wpbb_uploaded_file[ 'name' ] != '' ) {
			
			/* set overides to make it work */
			$wpbb_upload_overrides = array( 'test_form' => false );
			
			/* upload the file to wp uploads dir */
			$wpbb_moved_file = wp_handle_upload( $wpbb_uploaded_file, $wpbb_upload_overrides );
			
			/* get file type of the uploaded file */
			$wpbb_filetype = wp_check_filetype( $wpbb_moved_file[ 'url' ], null );
			
			/* generate array of allowed mime types */
			$wpbb_allowed_mime_types = apply_filters(
				'wpbb_application_allowed_file_types',
				array(
					'application/pdf',
				)
			);
			
			/* check uploaded file is in allowed mime types array */
			if( ! in_array( $wpbb_filetype[ 'type' ], $wpbb_allowed_mime_types) ) {
				
				/* upload file not allowed - add to messages */
				$wpbb_messages[] = '<p class="message error">Error: CV is not an allowed file type.</p>';
				
			}
		
		}		
			
		/* get the wp upload directory */
		$wpbb_wp_upload_dir = wp_upload_dir();
		
		/* setup the attachment data */
		$wpbb_attachment = array(
		     'post_mime_type' => $wpbb_filetype[ 'type' ],
		     'post_title' => preg_replace('/\.[^.]+$/', '', $wpbb_uploaded_file[ 'name' ]),
		     'post_content' => '',
		     'guid' => $wpbb_wp_upload_dir[ 'url' ] . '/' . $wpbb_uploaded_file[ 'name' ],
		     'post_status' => 'inherit'
		);
		
		/* insert the application post */
		$wpbb_application_id = wp_insert_post(
			array(
				'post_type' => 'wpbb_application',
				'post_title' => wp_strip_all_tags( $_POST[ 'wpbb_name' ] ),
				'post_status' => 'publish'
			)
		);
		
		/* check the application post has been added */
		if( $wpbb_application_id != 0 ) {
			
			/* set the post meta data (custom fields) */
			add_post_meta( $wpbb_application_id, '_wpbb_job_reference', wp_strip_all_tags( $_POST[ 'wpbb_job_reference' ] ), true );
			add_post_meta( $wpbb_application_id, '_wpbb_applicant_email', wp_strip_all_tags( $_POST[ 'wpbb_email' ] ), true );
			
			/* check we have a file to attach */
			if( $wpbb_uploaded_file[ 'name' ] != '' ) {
			
				/* add the attachment from the uploaded file */
				$wpbb_attach_id = wp_insert_attachment( $wpbb_attachment, $wpbb_filetype[ 'file' ], $wpbb_application_id );
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				$wpbb_attach_data = wp_generate_attachment_metadata( $wpbb_attach_id, $wpbb_filetype[ 'file' ] );
				wp_update_attachment_metadata( $wpbb_attach_id, $wpbb_attach_data );
			
			}
			
			/* set an output message */
			$wpbb_messages[] = '<p class="message success">Thank you. You application has been received.</p>';
			
		} // end check application post added
				
		/* add filter below to allow / force mail to send as html */
		add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html"; ' ) );
		
		/* get the post object for the job being applied for */
		$job_post = get_post( $job_post );
		
		/* build the content of the email */
		$wpbb_email_content = '
		
			<p>' . $_POST[ 'wpbb_name' ] . ' has completed an application for ' . $job_post->the_title . ' which has the job reference of ' . $_POST[ 'wpbb_job_reference' ] . '. The applicants email address is ' . $_POST[ 'wpbb_email' ] . '. Below is a summary of their responses:</p>
			
			<ul>
				<li>Applicant Name: ' . esc_html( get_the_title( $wpbb_application_id ) ) . '</li>
				<li>Applicant Email Address: ' . esc_html( get_post_meta( $wpbb_application_id, '_wpbb_applicant_email', true ) ) . '</li>
				<li>Job Title: ' . esc_html( get_the_title( $job_post->ID ) ) . '</li>
				<li>Job Reference: ' . esc_html( get_post_meta( $job_post->ID, '_wpbb_job_reference', true ) ) . '</li>
				<li>Job Permalink: <a href="' . esc_url( get_permalink( $job_post->ID ) ) . '">' . esc_url( get_permalink( $job_post->ID ) ) . '</a></li>
				<li><a href="' . get_edit_post_link( $wpbb_application_id ) . '">Application Edit Link</a></li>
				<li><a href="' . esc_url( $wpbb_moved_file[ 'url' ] ) . '">CV Attachment Link</a></li>
			</ul>
			
			<p>Email sent by <a href="http://wpbroadbean.com">WP Broadbean WordPress plugin</a>.</p>
			
		';
		
		/* set up the mail variables */
		$wpbb_mail_subject = 'New Job Application Submitted - ' . esc_html( get_the_title( $wpbb_application_id ) );
		$wpbb_email_headers = 'From: ' . $_POST[ 'wpbb_name' ] . ' <' . $_POST[ 'wpbb_email' ] . '>';
		
		/**
		 * set the content of the email as a variable
		 * this is made filterable and is passed the job post object being applied for
		 * along with the application post id
		 * devs can use this filter to change the contents of the email sent
		 */
		$wpbb_mail_content = wpbb_generate_email_content( apply_filters( 'wpbb_application_email_content', $wpbb_email_content, $job_post, $wpbb_application_id ) );
		
		$wpbb_mail_recipients = get_post_meta( $job_post->ID, '_wpbb_job_contact_email', true ) . ',' . get_post_meta( $job_post->ID, '_wpbb_job_broadbean_application_email', true );
		
		/* set attachments - the cv */
		$wpbb_attachments = array( WP_CONTENT_DIR . '/uploads' . $wpbb_wp_upload_dir[ 'subdir' ] . '/' . $wpbb_uploaded_file[ 'name' ] );
		
		/* send the mail */
		$wpbb_send_email = wp_mail(
			$wpbb_mail_recipients,
			$wpbb_mail_subject,
			$wpbb_mail_content,
			$wpbb_email_headers,
			$wpbb_attachments
		);
		
		/* remove filter below to allow / force mail to send as html */
		remove_filter( 'wp_mail_content_type', create_function( '', 'return "text/html"; ' ) );
	
	} // end if form posted
	
}

add_action( 'wp', 'wpbb_application_processing', 10 );