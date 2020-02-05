<?php
/**
 * @package WP_Lever
 */

namespace WP_Lever\Services;


/**
 *
 */
class Schema_Service {
	/**
	 * @param array $atts
	 * @param \WP_Lever\Data_Transfer_Objects\Job_Posting|null $job_posting
	 *
	 * @return string
	 */
	public static function getJsonLDString( $atts, $job_posting ) {
		if ( $job_posting == null ) {
			return "";
		}

		$json_ld = array(
			"@context"           => "http://schema.org",
			"@type"              => "JobPosting",
			"title"              => $job_posting->get_title(),
			"description"        => $job_posting->get_description()->get_formatted(),
			"identifier"         => array(
				"@type" => "PropertyValue",
				"name"  => $job_posting->get_title(),
				"value" => $job_posting->get_id()
			),
			"jobLocation"        => array(
				"@type"   => "Place",
				"address" => array(
					"@type"           => "PostalAddress",
					"addressLocality" => $job_posting->get_categories()->get_location(),
					"addressRegion"   => $atts['options']['schema']['address_region'],
					"streetAddress"   => $atts['options']['schema']['street_address'],
					"postalCode"      => $atts['options']['schema']['postal_code'],
				)
			),
			"hiringOrganization" => array(
				"@type"      => "Organization",
				"name"       => $job_posting->get_categories()->get_department(),
				"sameAs"     => get_site_url(),
				"logo"       => $atts['options']['schema']['logo_url'],
				"department" => array(
					"@type" => "Organization",
					"name"  => $job_posting->get_categories()->get_team()
				)
			),
			"employmentType"     => self::employmentTypeFromCommitment( $job_posting->get_categories()->get_commitment() )
		);

		if ( $job_posting->get_created_at() != null ) {
			$json_ld["datePosted"] = $job_posting->get_created_at()->format( "Y-m-d\TH:i:sO" );
		}

		return json_encode( $json_ld, JSON_UNESCAPED_SLASHES );
	}

	/**
	 * @param string|null $commitment
	 *
	 * @return string|null
	 */
	private static function employmentTypeFromCommitment( $commitment ) {
		switch ( $commitment ) {
			case "Full time":
				return "FULL_TIME";
				break;
			case "Part time":
				return "PART_TIME";
				break;
			case "Intern":
			case "Internship":
			case "Working student":
				return "INTERN";
			default:
				return null;
		}
	}
}