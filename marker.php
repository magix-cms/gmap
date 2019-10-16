<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Magix CMS.
# Magix CMS, a CMS optimized for SEO
# Copyright (C) 2010 - 2011  Gerits Aurelien <aurelien@magix-cms.com>
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# -- END LICENSE BLOCK -----------------------------------
/**
 * Class plugins_gmap_marker
 *
 * Gmap marker class allows you to create a marker based on a color
 * It will create two version of the marker a normal and a dot-less one.
 *
 * @author     Salvatore Di Salvo <disalvo.infographiste@gmail.com>
 */
class plugins_gmap_marker {
	/**
	 * @var
	 */
	protected $template, $color, $rgb, $hsl;

	/**
	 * plugins_gmap_marker constructor.
	 * @param $hex
	 * @param $tpl
	 */
	function __construct($hex, $tpl) {
		$this->template = $tpl;
		$this->color = $hex;
		$this->rgb = sscanf($this->color, "#%2x%2x%2x"); // Get the rgb values of the color
		$this->hsl = $this->rgb2hsl($this->rgb[0], $this->rgb[1], $this->rgb[2]); // Get the hsl values of the color
	}

	/**
	 * Convert a rgb color into a hsl color
	 * @param $r
	 * @param $g
	 * @param $b
	 * @return array
	 */
	public function rgb2hsl($r, $g, $b) {
		$var_R = ($r / 255);
		$var_G = ($g / 255);
		$var_B = ($b / 255);

		$var_Min = min($var_R, $var_G, $var_B);
		$var_Max = max($var_R, $var_G, $var_B);
		$del_Max = $var_Max - $var_Min;

		$v = $var_Max;

		if ($del_Max == 0) {
			$h = 0;
			$s = 0;
		} else {
			$s = $del_Max / $var_Max;

			$del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
			$del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
			$del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

			if      ($var_R == $var_Max) $h = $del_B - $del_G;
			else if ($var_G == $var_Max) $h = ( 1 / 3 ) + $del_R - $del_B;
			else if ($var_B == $var_Max) $h = ( 2 / 3 ) + $del_G - $del_R;

			if ($h < 0) $h++;
			if ($h > 1) $h--;
		}

		return array($h, $s, $v);
	}

	/**
	 * Convert a hsl color into a rgb color
	 * @param $h
	 * @param $s
	 * @param $v
	 * @return array
	 */
	public function hsl2rgb($h, $s, $v) {
		if($s == 0) {
			$r = $g = $B = $v * 255;
		} else {
			$var_H = $h * 6;
			$var_i = floor( $var_H );
			$var_1 = $v * ( 1 - $s );
			$var_2 = $v * ( 1 - $s * ( $var_H - $var_i ) );
			$var_3 = $v * ( 1 - $s * (1 - ( $var_H - $var_i ) ) );

			if       ($var_i == 0) { $var_R = $v     ; $var_G = $var_3  ; $var_B = $var_1 ; }
			else if  ($var_i == 1) { $var_R = $var_2 ; $var_G = $v      ; $var_B = $var_1 ; }
			else if  ($var_i == 2) { $var_R = $var_1 ; $var_G = $v      ; $var_B = $var_3 ; }
			else if  ($var_i == 3) { $var_R = $var_1 ; $var_G = $var_2  ; $var_B = $v     ; }
			else if  ($var_i == 4) { $var_R = $var_3 ; $var_G = $var_1  ; $var_B = $v     ; }
			else                   { $var_R = $v     ; $var_G = $var_1  ; $var_B = $var_2 ; }

			$r = $var_R * 255;
			$g = $var_G * 255;
			$B = $var_B * 255;
		}
		return array($r, $g, $B);
	}

	/**
	 * Convert a rgb color into a hexadecimal color
	 * @param $rgb
	 * @return string
	 */
	public function rgb2hex($rgb) {
		$hex = '#';
		foreach ($rgb as $chan) {
			$seg = dechex($chan);
			if(strlen($seg) === 0) $seg = '00';
			if(strlen($seg) === 1) $seg = '0'.$seg;
			$hex .= $seg;
		}
		return $hex;
	}

	/**
	 * Set the stroke color of the marker
	 */
	private function setStrokeColor()
	{
		$s = $this->hsl[1] - 0.02;
		$s = $s < 0 ? 0 : $s;
		$l = $this->hsl[2] - 0.25;
		$l = $l < 0 ? 0 : $l;

		$strokeColor = $this->hsl2rgb($this->hsl[0], $s, $l);
		$this->template->assign('strokeColor',$this->rgb2hex($strokeColor));
	}

	/**
	 * Set the dot color of the marker
	 */
	private function setDotColor()
	{
		$s = $this->hsl[1] + 0.07;
		$s = $s > 1 ? 1 : $s;
		$l = $this->hsl[2] - 0.60;
		$l = $l < 0 ? 0 : $l;

		$strokeColor = $this->hsl2rgb($this->hsl[0], $s, $l);
		$this->template->assign('dotColor',$this->rgb2hex($strokeColor));
	}

	/**
	 * Set the background color of the marker
	 */
	private function setBgGradient()
	{
		$gradient = array(
			$this->color
		);

		for($i = 1; $i < 5; $i++) {
			$s = $this->hsl[1] - (floor((15 / 4) * $i) / 100);
			$s = $s < 0 ? 0 : $s;
			$l = $this->hsl[2] + (floor((3 / 4) * $i) / 100);
			$l = $l > 1 ? 1 : $l;

			$gradient[$i] = $this->rgb2hex($this->hsl2rgb($this->hsl[0], $s, $l));
		}

		$this->template->assign('gradient',$gradient);
	}

	/**
	 * Create a marker in both normal and dot-less versions
	 * @param string $name (optional)
	 */
	public function createMarker($name = 'main')
	{
		$this->setStrokeColor();
		$this->setBgGradient();

		file_put_contents(component_core_system::basePath().'/plugins/gmap/markers/'.$name.'-dotless.svg', $this->template->fetch('marker.tpl'));

		$this->setDotColor();

		file_put_contents(component_core_system::basePath().'/plugins/gmap/markers/'.$name.'.svg', $this->template->fetch('marker.tpl'));
	}
}