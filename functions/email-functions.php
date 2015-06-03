<?php
/**
 * Function wpbb_generate_email_header()
 * Generate the header HTML for emails
 * Must match the closing tags in wpbb_generate_email_header()
 * Plugable functions which can be overidden
 */
if( ! function_exists( 'wpbb_generate_email_header' ) ) {
	function wpbb_generate_email_header() {
	
		ob_start();
		?>
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
					<title>Latest Newsletter</title>
					<style type="text/css">
						.alignleft {
							float: left;
						}
						.alignright {
							float: right;
						}
						.aligncenter {
							display: block;
							margin-left: auto;
							margin-right: auto;
						}
						img {
							max-width: 100%;
						}
						</style>
					</head>
				<body style="font-family: Arial, Helvetica, Verdana, sans-serif;">
					<table>
						<tr>
							<td style="max-width: 650px; width:650px; padding: 15px 20px 15px 20px; border: 1px solid #363636; margin-left: auto; margin-right: auto; font-family: Arial, Helvetica, Verdana, sans-serif; font-size:12px;">
		
		<?php
		$wpbb_email_header = ob_get_clean();
		
		return $wpbb_email_header;
		
	}
}

/**
 * Function wpbb_generate_email_footer()
 * Generate the footer HTML for emails
 * Must match the opened tags in wpbb_generate_email_header()
 * Plugable functions which can be overidden
 */
if( ! function_exists( 'wpbb_generate_email_footer' ) ) {
	function wpbb_generate_email_footer() {
	
		ob_start();
		?>
							</td>
						</tr>
					</table>
				</body>
			</html>
		
		<?php
		$wpbb_email_footer = ob_get_clean();
		
		return $wpbb_email_footer;
	
	}
}

/**
 * Function wpbb_generate_email_content()
 * Creates the content of an email by adding the HTML tags at the
 * begining and the end of the body etc with the content in the
 * middle.
 * @param post content to put in email
 * @return complete HTML email to send
 */
function wpbb_generate_email_content( $wpbb_email_content ) {
	
	/* build email header */
	$wpbb_email_header = wpbb_generate_email_header();
	
	/* build email footer */
	$wpbb_email_footer = wpbb_generate_email_footer();
	
	/* build compltete email */
	$wpbb_complete_email = $wpbb_email_header . $wpbb_email_content . $wpbb_email_footer;
	
	return $wpbb_complete_email;
	
}

/**
 * function wpbb_email_content_type()
 */
function wpbb_email_content_type() {
	return 'text/html';
}


/**
 * function wpbb_send_application_email()
 */
function wpbb_send_application_email( $attachments, $wpbb_meta_save, $wpbb_application_id ) {
	
	/* add filter below to allow / force mail to send as html */
	add_filter( 'wp_mail_content_type', 'wpbb_email_content_type' );
	
	/* build the content of the email */
	$wpbb_email_content = '
	
		<p>' . sanitize_text_field( $wpbb_meta_save[ 'applicant_name' ] ) . ' has completed an application for ' . esc_html( get_the_title( $wpbb_meta_save[ 'job_post_id' ] ) ) . ' which has the job reference of ' . sanitize_text_field( $wpbb_meta_save[ 'job_reference' ] ) . '. The applicants email address is ' . sanitize_text_field( $wpbb_meta_save[ 'applicant_email' ] ) . '. Below is a summary of their responses:</p>
		
		<ul>
			<li>Applicant Name: ' . sanitize_text_field( $wpbb_meta_save[ 'applicant_name' ] ) . '</li>
			<li>Applicant Email Address: ' . sanitize_text_field( $wpbb_meta_save[ 'applicant_email' ] ) . '</li>
			<li>Job Title: ' . esc_html( get_the_title( $wpbb_meta_save[ 'job_post_id' ] ) )  . '</li>
			<li>Job Reference: ' . sanitize_text_field( $wpbb_meta_save[ 'job_reference' ] ) . '</li>
			<li>Job Permalink: <a href="' . esc_url( get_permalink( $wpbb_meta_save[ 'job_post_id' ] ) ) . '">' . esc_url( get_permalink( $wpbb_meta_save[ 'job_post_id' ] ) ) . '</a></li>
			<li><a href="' . get_edit_post_link( $wpbb_application_id ) . '">Application Edit Link</a></li>
		</ul>
		
		<p>Email sent by <a href="http://wpbroadbean.com">WP Broadbean WordPress plugin</a>.</p>
		
	';
	
	/* set up the mail variables */
	$wpbb_mail_subject = 'New Job Application Submitted - ' . esc_html( get_the_title( $wpbb_meta_save[ 'job_post_id' ] ) );
	$wpbb_email_headers = 'From: ' . sanitize_text_field( $wpbb_meta_save[ 'applicant_name' ] ) . ' <' . sanitize_text_field( $wpbb_meta_save[ 'applicant_email' ] ) . '>';
	
	/**
	 * set the content of the email as a variable
	 * this is made filterable and is passed the job post object being applied for
	 * along with the application post id
	 * devs can use this filter to change the contents of the email sent
	 */
	$wpbb_mail_content = wpbb_generate_email_content( apply_filters( 'wpbb_application_email_content', $wpbb_email_content, sanitize_text_field( $wpbb_meta_save[ 'job_post_id' ] ), $wpbb_application_id ) );
	
	$wpbb_mail_recipients = array(
		'mark@markwilkinson.me',
		//sanitize_text_field( $wpbb_meta_save[ 'tracking_email' ] )
	);
	
	/* send the mail */
	$wpbb_send_email = wp_mail(
		$wpbb_mail_recipients,
		$wpbb_mail_subject,
		$wpbb_mail_content,
		$wpbb_email_headers,
		$attachments
	);
	
	/* remove filter below to allow / force mail to send as html */
	remove_filter( 'wp_mail_content_type', 'wpbb_email_content_type' );
	
}