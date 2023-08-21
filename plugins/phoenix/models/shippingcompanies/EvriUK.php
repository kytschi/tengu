<?php

/**
 * Evri UK shipping company model.
 *
 * @package     Kytschi\Phoenix\Models\ShippingCompanies\EvriUK
 * @copyright   2023 Kytschi
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301, USA.
 */

namespace Kytschi\Phoenix\Models\ShippingCompanies;

use Kytschi\Phoenix\Models\ShippingCompanies\Model;
use Kytschi\Phoenix\Models\ShippingCompanies\Option;

class EvriUK extends Model
{
    public $code = 'evri_uk';
    public $name = 'Evri UK';
    public $logo = '<svg viewBox="0 0 76 32" fill="none" xmlns="http://www.w3.org/2000/svg" height="36" width="87"><g clip-path="url(#clip0_12073_83237)"><path fill-rule="evenodd" clip-rule="evenodd" d="M71.121 0c1.497 0 2.713 1.154 2.713 2.65 0 1.505-1.216 2.65-2.713 2.65-1.496 0-2.713-1.154-2.713-2.65 0-1.505 1.217-2.65 2.713-2.65ZM18.695.144V5.86h-3.66V3.543H6.23v5.723h8.004v3.246H6.23v5.778h8.806v-2.317h3.66v5.724H.235V18.29h2.028V3.543H.234V.144h18.461Zm12.908 15.207L37.508.144h2.253l-8.563 21.652h-2.362L20.228.144h5.525l5.85 15.207Zm12.846-4.832V2.776h11.52c1.928 0 3.028.325 3.776 1.01.667.613 1.046 1.496 1.046 2.677 0 1.217-.333 2.218-1.01 2.93-.703.739-1.757 1.135-3.74 1.135H44.449v-.009Zm21.507 11.178-7.617-8.798c3.236-.333 5.688-2.677 5.688-6.472 0-1.92-.577-3.39-1.668-4.417C61.016.74 59.07.144 55.97.144H41.311v21.553h3.137v-8.545h10.393l7.256 8.545h3.858Zm7.527-2.903c0 1.262.523 1.776 1.685 2.362v.54h-7.86v-.54c1.163-.577 1.686-1.1 1.686-2.362v-8.248c0-1.117-.198-1.73-1.686-2.722v-.54h6.175v11.51ZM53.39 26.555h.776v-.712h-.776v-.297c0-.244.054-.415.163-.514.099-.099.288-.144.55-.144v-.721c-.541 0-.947.117-1.2.333-.26.226-.387.577-.387 1.055v.297h-.496v.703h.496v3.48h.874v-3.48ZM11.051 25.14a.53.53 0 0 1-.162-.396c0-.154.054-.289.162-.397a.53.53 0 0 1 .397-.162c.153 0 .28.054.387.162a.53.53 0 0 1 .163.397.545.545 0 0 1-.162.396.525.525 0 0 1-.388.162.608.608 0 0 1-.397-.162Zm.82.712v4.183h-.865v-4.183h.865Zm-11.61.956c.172-.325.415-.577.713-.757.297-.18.64-.27 1.01-.27.27 0 .54.062.81.18.262.117.478.279.631.477V24.42H4.3v5.616h-.875v-.631c-.144.198-.333.37-.586.505a1.8 1.8 0 0 1-.865.198 1.915 1.915 0 0 1-1.713-1.055A2.415 2.415 0 0 1 0 27.926c0-.424.09-.794.261-1.118Zm2.984.378a1.165 1.165 0 0 0-.469-.486 1.24 1.24 0 0 0-1.712.469c-.118.207-.18.46-.18.748s.062.54.18.757c.117.216.28.387.468.496.199.117.397.17.622.17.226 0 .433-.053.622-.17.19-.109.352-.27.47-.487a1.58 1.58 0 0 0 .18-.757c0-.28-.064-.532-.18-.74ZM8.87 28.26H5.679c.027.334.153.604.37.802a1.2 1.2 0 0 0 .82.307c.468 0 .793-.198.982-.586h.938a1.85 1.85 0 0 1-.685.947c-.334.243-.74.369-1.235.369a2.13 2.13 0 0 1-1.073-.27 1.95 1.95 0 0 1-.748-.758c-.18-.324-.27-.703-.27-1.135 0-.433.09-.812.26-1.136.172-.325.425-.577.74-.757.316-.18.676-.262 1.09-.262.397 0 .749.09 1.055.262.307.171.55.414.722.72.17.316.26.668.26 1.073a4.99 4.99 0 0 1-.035.424Zm-.875-.694c-.009-.315-.117-.577-.342-.766a1.238 1.238 0 0 0-.83-.289c-.297 0-.55.1-.766.289a1.16 1.16 0 0 0-.378.766h2.316Zm2.245 2.47V24.42h-.865v5.616h.865Zm5.192-4.183-1.18 3.408-1.182-3.408h-.928l1.578 4.183h1.036l1.596-4.183h-.92Zm5.012 2.407h-3.191c.027.334.153.604.37.802a1.2 1.2 0 0 0 .82.307c.468 0 .793-.198.982-.586h.938a1.85 1.85 0 0 1-.685.947c-.334.243-.74.369-1.235.369-.397 0-.757-.09-1.073-.27a1.948 1.948 0 0 1-.748-.758c-.18-.324-.27-.703-.27-1.135 0-.433.09-.812.261-1.136a1.86 1.86 0 0 1 .74-.757c.315-.18.675-.262 1.09-.262.396 0 .748.09 1.054.262.307.171.55.414.722.72.17.316.261.668.261 1.073a4.951 4.951 0 0 1-.036.424Zm-.874-.694c-.01-.315-.118-.577-.343-.766a1.238 1.238 0 0 0-.83-.289c-.297 0-.55.1-.765.289-.217.19-.343.45-.379.766h2.317Zm3.497-1.785c-.288 0-.54.063-.748.18a1.352 1.352 0 0 0-.496.487v-.604h-.865v4.183h.865v-2.2c0-.423.09-.72.261-.892.172-.171.424-.261.767-.261h.216v-.893Zm4.49.072L24.986 32h-.892l.847-2.037-1.65-4.11h.965l1.18 3.19 1.227-3.19h.892Zm8.436.74a1.467 1.467 0 0 0-.613-.604 1.906 1.906 0 0 0-.883-.208c-.307 0-.595.072-.865.226-.27.153-.47.351-.595.604a1.417 1.417 0 0 0-.604-.613 1.817 1.817 0 0 0-.893-.217c-.243 0-.477.045-.694.144a1.54 1.54 0 0 0-.54.406v-.478h-.866v4.183h.865V27.7c0-.378.1-.667.289-.865.19-.198.441-.297.766-.297s.577.099.766.297c.19.207.28.487.28.865v2.335h.856V27.7c0-.378.1-.667.288-.865.19-.198.442-.297.767-.297.324 0 .577.099.766.297.19.207.28.487.28.865v2.335h.856v-2.47c0-.379-.072-.703-.226-.973Zm.929.216c.171-.325.414-.577.712-.757.297-.18.63-.27 1-.27.325 0 .613.062.866.189.243.126.441.288.586.477v-.604h.874v4.183h-.874v-.622c-.145.198-.343.36-.604.496a1.807 1.807 0 0 1-.866.198c-.36 0-.685-.09-.982-.28a1.98 1.98 0 0 1-.712-.775 2.416 2.416 0 0 1-.262-1.126c0-.424.09-.785.262-1.11Zm2.983.378a1.165 1.165 0 0 0-.468-.486 1.24 1.24 0 0 0-1.713.469c-.117.207-.18.46-.18.748s.063.54.18.757c.117.216.28.387.469.496.198.117.396.17.622.17.225 0 .432-.053.622-.17.189-.109.351-.27.468-.487.118-.216.18-.469.18-.757 0-.28-.062-.532-.18-.74Zm2.506-1.135c-.297.18-.54.432-.712.757-.18.324-.261.694-.261 1.118 0 .423.09.793.261 1.126a1.914 1.914 0 0 0 1.713 1.055c.324 0 .613-.063.865-.198.253-.136.442-.307.586-.505v.63h.874V24.42h-.874v2.02a1.628 1.628 0 0 0-.63-.478 2.026 2.026 0 0 0-.812-.18c-.37 0-.712.09-1.01.27Zm1.803.649c.198.108.352.27.469.486.117.208.171.46.18.74 0 .288-.063.54-.18.757a1.22 1.22 0 0 1-.469.487c-.19.117-.396.17-.622.17a1.18 1.18 0 0 1-.622-.17 1.247 1.247 0 0 1-.469-.496 1.568 1.568 0 0 1-.18-.757c0-.289.063-.541.18-.748a1.231 1.231 0 0 1 1.09-.64c.227 0 .434.063.623.17Zm6.085 1.56h-3.191c.027.333.153.603.37.801a1.2 1.2 0 0 0 .82.307c.468 0 .793-.198.982-.586h.938a1.85 1.85 0 0 1-.686.947c-.333.243-.739.369-1.234.369-.397 0-.758-.09-1.073-.27a1.95 1.95 0 0 1-.748-.758c-.18-.324-.27-.703-.27-1.135 0-.433.09-.812.26-1.136.172-.325.424-.577.74-.757.315-.18.676-.262 1.09-.262.397 0 .749.09 1.055.262.307.171.55.414.721.72.172.316.262.668.262 1.073a4.992 4.992 0 0 1-.036.424Zm-.875-.695c-.009-.315-.117-.577-.342-.766a1.238 1.238 0 0 0-.83-.289c-.297 0-.55.1-.766.289a1.16 1.16 0 0 0-.378.766h2.316Zm5.877 2.263a1.95 1.95 0 0 1-.748-.758c-.18-.324-.27-.703-.27-1.135 0-.424.09-.803.28-1.127.188-.325.44-.586.765-.757.325-.18.685-.262 1.082-.262s.757.09 1.082.262c.324.18.577.432.766.757.19.324.28.703.28 1.127 0 .423-.1.802-.289 1.126-.19.334-.45.586-.784.767-.334.18-.694.27-1.1.27-.388 0-.748-.09-1.064-.27Zm1.695-.64a1.22 1.22 0 0 0 .478-.478c.126-.216.18-.469.18-.775 0-.307-.054-.56-.171-.766a1.236 1.236 0 0 0-.46-.478 1.232 1.232 0 0 0-.622-.162c-.225 0-.433.054-.622.162-.19.108-.342.261-.45.478a1.659 1.659 0 0 0-.172.766c0 .45.117.802.343 1.045.234.244.523.37.865.37.225 0 .433-.054.631-.162Zm4.146-3.408c-.288 0-.54.063-.748.18a1.352 1.352 0 0 0-.495.487v-.604h-.866v4.183h.866v-2.2c0-.423.09-.72.261-.892.171-.171.424-.261.766-.261h.216v-.893Zm5.797.072L64.369 32h-.892l.848-2.037-1.65-4.11h.964l1.181 3.19 1.226-3.19h.893Zm.117 3.218c.18.325.433.577.748.758.325.18.676.27 1.064.27.405 0 .766-.09 1.1-.27.333-.18.594-.433.784-.767.189-.324.288-.703.288-1.126 0-.424-.09-.803-.28-1.127a1.995 1.995 0 0 0-.766-.757 2.284 2.284 0 0 0-1.081-.262c-.397 0-.757.081-1.082.262a1.934 1.934 0 0 0-.766.757c-.19.324-.28.703-.28 1.127 0 .432.09.81.27 1.135Zm2.93-.36c-.127.216-.28.37-.478.478a1.29 1.29 0 0 1-.631.162c-.343 0-.631-.126-.866-.37-.225-.243-.342-.595-.342-1.045 0-.298.063-.56.171-.766.108-.217.261-.37.45-.478.19-.108.397-.162.623-.162.225 0 .432.054.622.162.189.108.342.27.46.478.117.207.17.46.17.766s-.053.559-.18.775Zm5.336-2.858v4.183h-.866v-.496a1.42 1.42 0 0 1-.532.406c-.216.099-.45.144-.703.144-.324 0-.622-.072-.883-.207a1.466 1.466 0 0 1-.613-.604c-.153-.27-.225-.595-.225-.974v-2.46h.856v2.325c0 .379.09.658.28.865.189.199.441.298.766.298.324 0 .577-.1.766-.298.19-.198.288-.486.288-.865v-2.326l.866.01Z" fill="#007BC4"></path></g><defs><clipPath id="clip0_12073_83237"><path fill="#fff" d="M0 0h75.322v32H0z"></path></clipPath></defs></svg>';

    public function __construct()
    {
        $this->options[] = new Option(
            [
                'code' => 'next_day',
                'name' => 'Next day postable',
                'price_drop_off' => '3.29',
                'price_collection' => null,
                'weight_from' => '0',
                'weight_to' => '1',
                'postable' => true,
                'width' => '23',
                'height' => '3',
                'length' => '35',
            ]
        );

        $this->options[] = new Option(
            [
                'code' => 'standard',
                'name' => 'Standard 2-3 days postable',
                'price_drop_off' => '2.69',
                'price_collection' => null,
                'weight_from' => '0',
                'weight_to' => '1',
                'postable' => true,
                'width' => '23',
                'height' => '3',
                'length' => '35',
            ]
        );
    }
}
