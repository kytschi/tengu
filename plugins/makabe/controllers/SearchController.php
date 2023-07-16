<?php

/**
 * Search controller.
 *
 * @package     Kytschi\Makabe\Controllers\SearchController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Makabe\Controllers;

use Kytschi\Makabe\Models\Campaigns;
use Kytschi\Makabe\Models\Keywords;
use Kytschi\Makabe\Models\Personas;
use Kytschi\Makabe\Models\ScanPages;
use Kytschi\Tengu\Controllers\ControllerBase;

class SearchController extends ControllerBase
{
    public function searchQuery($query)
    {
        //Keywords
        $table = (new Keywords())->getSource();
        $query .= " UNION ";
        $query .= "SELECT 
                    id,
                    '/mms/keywords' AS tengu_url,
                    'keyword' AS type,
                    keyword as name,
                    '' AS content,
                    IF(deleted_at IS NULL, 'active', 'deleted') AS status,
                    updated_at,
                    '" . urlencode($this->setIcon('keyword')) . "' AS icon
                FROM $table
                WHERE keyword LIKE :search";

        //Scanned pages
        $table = (new ScanPages())->getSource();
        $query .= " UNION ";
        $query .= "SELECT 
                    id,
                    '/mms/page-scanner' AS tengu_url,
                    'scanned-page' AS type,
                    name,
                    '' AS content,
                    status,
                    updated_at,
                    '" . urlencode($this->setIcon('scanned-page')) . "' AS icon
                FROM $table
                WHERE name LIKE :search";

        //Campaigns
        $table = (new Campaigns())->getSource();
        $query .= " UNION ";
        $query .= "SELECT 
                    id,
                    '/mms/seo-campaigns' AS tengu_url,
                    'seo-campaign' AS type,
                    name,
                    '' AS content,
                    status,
                    updated_at,
                    '" . urlencode($this->setIcon('seo-campaign')) . "' AS icon
                FROM $table
                WHERE name LIKE :search AND type='seo'";

        //Personas
        $table = (new Personas())->getSource();
        $query .= " UNION ";
        $query .= "SELECT 
                    id,
                    '/mms/personas' AS tengu_url,
                    'persona' AS type,
                    CONCAT(first_name, ' ', last_name) AS name,
                    '' AS content,
                    status,
                    updated_at,
                    '" . urlencode($this->setIcon('persona')) . "' AS icon
                FROM $table
                WHERE first_name LIKE :search OR last_name LIKE :search";

        return $query;
    }

    private function setIcon($type)
    {
        switch ($type) {
            case 'keyword':
                return '<span class="mr-4" title="I\'m a keyword"
                    data-toggle="tooltip" data-trigger="hover" data-dismiss="click" data-placement="top">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6 8V1h1v6.117L8.743 6.07a.5.5 0 0 1 .514 0L11 7.117V1h1v7a.5.5 0 0 1-.757.429L9 7.083 6.757 8.43A.5.5 0 0 1 6 8z"/>
                        <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2z"/>
                        <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1z"/>
                    </svg>
                    </span>';
            case 'persona':
                return '<span class="mr-4" title="I\'m a persona"
                    data-toggle="tooltip" data-trigger="hover" data-dismiss="click" data-placement="top">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                        <path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0h-7zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492V2.5z"/>
                    </svg>
                    </span>';
            case 'scanned-page':
                return '<span class="mr-4" title="I\'m an scanned page"
                    data-toggle="tooltip" data-trigger="hover" data-dismiss="click" data-placement="top">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M7.646 10.854a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 9.293V5.5a.5.5 0 0 0-1 0v3.793L6.354 8.146a.5.5 0 1 0-.708.708l2 2z"/>
                        <path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383zm.653.757c-.757.653-1.153 1.44-1.153 2.056v.448l-.445.049C2.064 6.805 1 7.952 1 9.318 1 10.785 2.23 12 3.781 12h8.906C13.98 12 15 10.988 15 9.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 4.825 10.328 3 8 3a4.53 4.53 0 0 0-2.941 1.1z"/>
                    </svg>
                    </span>';
            case 'seo-campaign':
                return '<span class="mr-4" title="I\'m an SEO campaign"
                    data-toggle="tooltip" data-trigger="hover" data-dismiss="click" data-placement="top">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10.354 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                        <path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383zm.653.757c-.757.653-1.153 1.44-1.153 2.056v.448l-.445.049C2.064 6.805 1 7.952 1 9.318 1 10.785 2.23 12 3.781 12h8.906C13.98 12 15 10.988 15 9.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 4.825 10.328 3 8 3a4.53 4.53 0 0 0-2.941 1.1z"/>
                    </svg>
                    </span>';
            default:
                return '';
                break;
        }
    }
}
