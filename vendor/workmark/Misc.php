<?php
namespace Workmark;

class Misc
{
	/**
	 * return string
	 */
	public static function dateToHuman($dateString)
	{
		$date = new \DateTime($dateString);
		return $date->format('M d, Y');
	}
	
	/**
	 * return string
	 */
	public static function datetimeToHuman($dateString)
	{
		$date = new \DateTime($dateString);
		return $date->format('H:i - M d, Y');
	}
	
	public static function minMaxDistance($lat,$lng,$distance)
	{
		
		// earth's radius in km = ~6371
		$radius = 6371;
		
		// latitude boundaries
		$maxlat = $lat + rad2deg($distance / $radius);
		$minlat = $lat - rad2deg($distance / $radius);
		
		// longitude boundaries (longitude gets smaller when latitude increases)
		$maxlng = $lng + rad2deg($distance / $radius / cos(deg2rad($lat)));
		$minlng = $lng - rad2deg($distance / $radius / cos(deg2rad($lat)));
		return array("N" => $maxlat,
				"E" => $maxlng,
				"S" => $minlat,
				"W" => $minlng);
	}
	
	// calculate distance between two lat/lon coordinates
	public static function distance($latA,$lonA, $latB,$lonB, $units="km") {
		$radius = strcasecmp($units, "km") ? 3963.19 : 6378.137;
		$rLatA = deg2rad($latA);
		$rLatB = deg2rad($latB);
		$rHalfDeltaLat = deg2rad(($latB - $latA) / 2);
		$rHalfDeltaLon = deg2rad(($lonB - $lonA) / 2);
		
		return 2 * $radius * asin(sqrt(pow(sin($rHalfDeltaLat), 2) +
				cos($rLatA) * cos($rLatB) * pow(sin($rHalfDeltaLon), 2)));
	}
	
	public static function base64_url_encode($input) {
		return base64_encode($input);
	}
}