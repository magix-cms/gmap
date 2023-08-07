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
/**
 * @category plugin
 * @package gmap
 * @copyright MAGIX CMS Copyright (c) 2011 Gerits Aurelien, http://www.magix-dev.be, http://www.magix-cms.com
 * @license Dual licensed under the MIT or GPL Version 3 licenses.
 * @version 1.0
 * @create 20-12-2021
 * @author Aurélien Gérits <aurelien@magix-cms.com>
 * @name plugins_gmap_marker
 */
class plugins_gmap_marker {
	/**
	 * @var frontend_model_template|backend_model_template $template
	 * @var string $color
	 * @var string $rgb
	 * @var string $hsl
	 */
	protected $template;
	protected string $color;
	protected array $rgb;
	protected array $hsl;

	/**
	 * plugins_gmap_marker constructor.
	 * @param string $hex
	 * @param frontend_model_template|backend_model_template $tpl
	 */
	function __construct(string $hex, $tpl) {
		$this->template = $tpl;
		$this->color = $hex;
		$this->rgb = sscanf($this->color, "#%2x%2x%2x"); // Get the rgb values of the color
		$this->hsl = $this->rgb2hsl($this->rgb[0], $this->rgb[1], $this->rgb[2]); // Get the hsl values of the color
	}

	/**
	 * Convert a rgb color into a hsl color
	 * @param int $r
	 * @param int $g
	 * @param int $b
	 * @return array
	 */
	public function rgb2hsl(int $r, int $g, int $b): array {
		$var_R = ($r / 255);
		$var_G = ($g / 255);
		$var_B = ($b / 255);

		$var_Min = min($var_R, $var_G, $var_B);
		$var_Max = max($var_R, $var_G, $var_B);
		$del_Max = $var_Max - $var_Min;

		$l = $var_Max;

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

		return [$h, $s, $l];
	}

	/**
	 * Convert a hsl color into a rgb color
	 * @param float $h
	 * @param float $s
	 * @param float $l
	 * @return array
	 */
	public function hsl2rgb(float $h, float $s, float $l): array {
		if($s == 0) {
			$r = $g = $B = $l * 255;
		} else {
			$var_H = $h * 6;
			$var_i = floor( $var_H );
			$var_1 = $l * ( 1 - $s );
			$var_2 = $l * ( 1 - $s * ( $var_H - $var_i ) );
			$var_3 = $l * ( 1 - $s * (1 - ( $var_H - $var_i ) ) );

			if       ($var_i == 0) { $var_R = $l     ; $var_G = $var_3  ; $var_B = $var_1 ; }
			else if  ($var_i == 1) { $var_R = $var_2 ; $var_G = $l      ; $var_B = $var_1 ; }
			else if  ($var_i == 2) { $var_R = $var_1 ; $var_G = $l      ; $var_B = $var_3 ; }
			else if  ($var_i == 3) { $var_R = $var_1 ; $var_G = $var_2  ; $var_B = $l     ; }
			else if  ($var_i == 4) { $var_R = $var_3 ; $var_G = $var_1  ; $var_B = $l     ; }
			else                   { $var_R = $l     ; $var_G = $var_1  ; $var_B = $var_2 ; }

			$r = $var_R * 255;
			$g = $var_G * 255;
			$B = $var_B * 255;
		}
		return [$r, $g, $B];
	}

	/**
	 * Convert a rgb color into a hexadecimal color
	 * @param array $rgb
	 * @return string
	 */
	public function rgb2hex(array $rgb): string {
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
	private function setStrokeColor() {
		if(!empty($this->hsl)) {
			$s = $this->hsl[1] - 0.02;
			$s = max($s, 0);
			$l = $this->hsl[2] - 0.25;
			$l = max($l, 0);

			$strokeColor = $this->hsl2rgb($this->hsl[0], $s, $l);
			$this->template->assign('strokeColor',$this->rgb2hex($strokeColor));
		}
	}

	/**
	 * Set the dot color of the marker
	 */
	private function setDotColor() {
		if(!empty($this->hsl)) {
			$s = $this->hsl[1] + 0.07;
			$s = min($s, 1);
			$l = $this->hsl[2] - 0.60;
			$l = max($l, 0);

			$strokeColor = $this->hsl2rgb($this->hsl[0], $s, $l);
			$this->template->assign('dotColor',$this->rgb2hex($strokeColor));
		}
	}

	/**
	 * Set the background color of the marker
	 */
	private function setBgGradient() {
		if(!empty($this->color)) {
			$gradient = [$this->color];

			for($i = 1; $i < 5; $i++) {
				$s = $this->hsl[1] - (floor((15 / 4) * $i) / 100);
				$s = max($s, 0);
				$l = $this->hsl[2] + (floor((3 / 4) * $i) / 100);
				$l = min($l, 1);

				$gradient[$i] = $this->rgb2hex($this->hsl2rgb($this->hsl[0], $s, $l));
			}

			$this->template->assign('gradient',$gradient);
		}
	}

	/**
	 * Create a marker in both normal and dot-less versions
	 * @param string $name (optional)
	 */
	public function createMarker(string $name = 'main') {
		$this->setStrokeColor();
		$this->setBgGradient();

		file_put_contents(component_core_system::basePath().'/plugins/gmap/markers/'.$name.'-dotless.svg', $this->template->fetch('marker.tpl'));

		$this->setDotColor();

		file_put_contents(component_core_system::basePath().'/plugins/gmap/markers/'.$name.'.svg', $this->template->fetch('marker.tpl'));
	}
}