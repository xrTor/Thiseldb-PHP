<?php

/**
 * PHP IMDb.com Grabber
 *
 * This PHP library enables you to scrape data from IMDB.com.
 *
 *
 * If you want to thank me for this library, please buy me something at Amazon
 * (https://www.amazon.de/hz/wishlist/ls/8840JITISN9L/) or use
 * https://www.paypal.me/FabianBeiner. Thank you! 🙌
 *
 * @author  Fabian Beiner <fb@fabianbeiner.de>
 * @license https://opensource.org/licenses/MIT The MIT License
 * @link    https://github.com/FabianBeiner/PHP-IMDB-Grabber/ GitHub Repository
 * @version 6.2.6
 */
class IMDB
{
    /**
     * Set this to true if you run into problems.
     */
    const IMDB_DEBUG = false;

    /**
     * Set the preferred language for the User Agent.
     */
    const IMDB_LANG = 'en-US,en;q=0.9';
   
     /**
     * Set this to true if you want to start with normal search and
     * if you get no result, it will use the advanced method
     */
    const IMDB_SEARCH_ORIGINAL = true;

    /**
     * Set this to true if you want to search for exact titles
     * it falls back to false if theres no result
     */
    const IMDB_EXACT_SEARCH = true;

    /**
     * Set the sensitivity for search results in percentage.
     */
    const IMDB_SENSITIVITY = 85;
    
    /**
     * Define the timeout for cURL requests.
     */
    const IMDB_TIMEOUT = 15;

    /**
     * These are the regular expressions used to extract the data.
     * If you don’t know what you’re doing, you shouldn’t touch them.
     */
    const IMDB_AKA           = '~=ttrv_dt_aka.*<span[^>]+>(.*)</span>~Ui';
    const IMDB_ASPECT_RATIO  = '~aspect ratio.*<span.*>(.*)</span>~Uis';
    const IMDB_AWARDS        = '~Awards</a>.*span\sclass.*>(.*)</div>~Uis';
    const IMDB_BUDGET        = '~budget</span>.*<span.*>\s*(.*)(?:\s*\(estimated\))\s*</span>~Ui';
    const IMDB_CAST          = '~<\/div><a class="ipc-lockup-overlay ipc-focusable" href="\/name\/([^\/]*)\/.*href.*>(.*)<\/a>~Ui';
    const IMDB_CAST_IMAGE    = '~(loadlate="(.*)"[^>]*><\/a>\s+<\/td>\s+)?<td[^>]*itemprop="actor"[^>]*>\s*<a\s*href="\/name\/([^/]*)\/\?[^"]*"[^>]*>\s*<span.+>(.+)<\/span+~Uis';
    const IMDB_CERTIFICATION = '~\?certificates=.*ref_=ttrv_stry">(.+)(?:</span></li></ul></div>|<a\sclass[^>]+\/parentalguide\/[^>]+>)~Ui';
    const IMDB_CHAR          = '~\/characters\/nm\d+\/.*>(.*)<\/a>~Ui';
    const IMDB_COLOR         = '~href="/search/title/\?colors(?:.*)">(.*)<\/a>~Ui';
    const IMDB_COMPANIES     = '~id="production">[\w\s]*?(.*)</section>~Ui';
    const IMDB_COMPANY       = '~href="/company/(co\d+)/\?ref_=ttrv_cmpy_\d">([^<svg].*?)</a><div~';
    const IMDB_COUNTRY       = '~country_of_origin=(.*)&amp;ref_=ttrv_dt_cnt">(.*)<\/a~Ui';
    const IMDB_CREATOR       = '~>\s*(?:Creator|Creators|Producer|Producers).*<ul[^>]*>(.+)</ul>~Uxsi';
    const IMDB_DISTRIBUTOR   = '@<span\sid="distribution".*<ul\sclass[^>]+>(.*)</section>@Uis';
    const IMDB_DISTRIBUTORS  = '@<li.*\/company\/(.*)\/[^>]+>(.*)<.*\((.*),\s*([\d-]+?).*\((.*)\).*</li>@Uis';
    const IMDB_DIRECTOR      = '~id="director".*<ul[^>]*>(.+)</section>~Uxsi';
    const IMDB_GENRE         = '~genres=([a-zA-Z_-]*)&amp;.*<span class="ipc-chip__text">([a-zA-Z_ -]*)<\/span><\/a~Ui';
    const IMDB_GROSS         = '~pl-zebra-list__label">Cumulative Worldwide Gross<\/td>\s*<td>\s*(.*?)\s*<\/td>~i';
    const IMDB_ID            = '~((?:tt\d+)|(?:itle\?\d+))~';
    const IMDB_LANGUAGE      = '~<a href="\/language\/(\w+)">(.*)<\/a>~Ui';
    const IMDB_LOCATION      = '~href="/search/title/\?locations=(.*)&amp.*">(.*)<\/a>~Ui';
    const IMDB_LOCATIONS     = '~href="(?<url>\/search\/title\/\?locations=[^>]*)">\s?(?<location>.*)\s?<\/a><p(.*)>\((?<specification>.*)\)<\/p>~Ui';
    const IMDB_MPAA          = '~<li class="ipl-inline-list__item">(?:\s+)(TV-Y|TV-Y7|TV-Y7-FV|TV-G|TV-PG|TV-14|TV-MA|TV-MA-L|TV-MA-S|TV-MA-V|G|PG|PG-13|R|NC-17|NR|UR|M|X)(?:\s+)<\/li>~Ui';
    const IMDB_MUSIC         = '~id="composer">.*<ul\sclass[^>]+>(.*)</section>~Uxsi';
    const IMDB_NAME          = '~href="/name/(.+)/?(?:\?[^"]*)?"[^>]*>(.+)</a>~Ui';
    const IMDB_MOVIE_DESC    = '~<section class="titlereference-section-overview">\s+<div>\s+(.*)\s*?</div>\s+<hr>\s+<div class="titlereference-overview-section">~Ui';
    const IMDB_SERIES_DESC   = '~<div>\s+(?:.*?</a>\s+</span>\s+</div>\s+<hr>\s+<div>\s+)(.*)\s+</div>\s+<hr>\s+<div class="titlereference-overview-section">~Ui';
    const IMDB_SERIESEP_DESC = '~All Episodes(?:.*?)</li>\s+(?:.*?)?</ul>\s+</span>\s+<hr>\s+</div>\s+<div>\s+(.*?)\s+</div>\s+<hr>~';
    const IMDB_NOT_FOUND_ADV = '~"results-section-empty-results-msg"~Ui';
    const IMDB_NOT_FOUND_DES = 'Know what this is about';
    const IMDB_NOT_FOUND_ORG = '~<h1 class="findHeader">No results found for ~Ui';
    const IMDB_PLOT          = '~data-testid="plot-l".*>(.*)<\/span>~Ui';
    const IMDB_PLOT_KEYWORDS = '~explore=keywords.*<span class="ipc-chip__text">(.*)<\/span>~Ui';
    const IMDB_POSTER        = '~<meta property="og:image" content="(.*)"\/>~Ui';
    const IMDB_RATING        = '~"ratingsSummary":{"aggregateRating":(.*),.*}~Ui';
    const IMDB_RATING_COUNT  = '~"ratingsSummary":{.*"voteCount":(\d+),.*}~Ui';
    const IMDB_RELEASE_DATE  = '~\/title\/tt\d+\/releaseinfo\/\?ref_=ttrv_ov_rdat">(.*)</a>~Ui';
    const IMDB_RUNTIME       = '~id="runtime".*<ul[^>]+>(.*)</ul>~Ui';
    const IMDB_SEARCH_ADV    = '~<a href="/title/(tt\d+).*?ipc-title-link-wrapper~i';
    const IMDB_SEARCH_ORG    = '~find-title-result">(?:.*?)alt="(.*?)"(?:.*?)href="\/title\/(tt\d{6,})\/(?:.*?)">(.*?)<\/a>~';
    const IMDB_SEASONS       = '~episodes/\?season=[^>]+>(\d+)<~Ui';
    const IMDB_SOUND_MIX     = '~/search/title/\?sound_mixes.*ref_=spec_2">(.*)</a>~Ui';
    const IMDB_TAGLINE       = '~"taglines":{"edges":\[{"node":{"text":"(.*)","__typename":"Tagline"}~Ui';
    const IMDB_TITLE         = '~<title>(.*)\s*\(.*\)\s*-\sReference\s*view \s*-\s*IMDb</title>~Ui';
    const IMDB_TITLE_EP      = '~titlereference-watch-ribbon"(?:.*)itemprop="name">(.*?)\s+<span\sclass="titlereference-title-year">~Ui';
    const IMDB_TITLE_ORIG    = '~hero__pageTitle.*hero__primary-text">(.*)</span>~Ui';
    const IMDB_TOP250        = '~href="/chart/top(?:tv)?".class(?:.*?)#([0-9]{1,})</a>~Ui';
    const IMDB_TRAILER       = '~href="/title/(?:tt\d+)/videoplayer/(vi[0-9]*)"~Ui';
    const IMDB_TYPE          = '~"titleType":.*"text":"(.*)",~Ui';
    const IMDB_URL           = '~https?://(?:.*\.|.*)imdb.com/(?:t|T)itle(?:\?|/)(..\d+)~i';
    const IMDB_USER_REVIEW   = '~href="/title/tt\d+/reviews/\?ref_=ttrv_ov_ururv">(.*)</a>~Ui';
    const IMDB_VOTES         = '~"ratingsSummary":{.*"voteCount":(\d+),.*}~Ui';
    const IMDB_WRITER        = '~>\s*(?:Writer|Writers).*<ul[^>]*>(.+)</ul>~Uxsi';
    const IMDB_YEAR          = '~<title>.*\s*\((?:[^()]+ )?(\d{4}(?:–\d{4})?)\)\s*-\sReference\s*view \s*-\s*IMDb</title>~iU';

    /**
     * @var string The string returned, if nothing is found.
     */
    public static $sNotFound = 'n/A';

    /**
     * @var null|int The ID of the movie.
     */
    public $iId = null;

    /**
     * @var bool Is the content ready?
     */
    public $isReady = false;

    /**
     * @var string Char that separates multiple entries.
     */
    public $sSeparator = ' / ';

    /**
     * @var null|string The URL to the movie.
     */
    public $sUrl = null;

    /**
     * @var bool Return responses enclosed in array
     */
    public $bArrayOutput = false;

    /**
     * @var int Maximum cache time.
     */
    private $iCache = 1440;

    /**
     * @var null|string The root of the script.
     */
    private $sRoot = null;

    /**
     * @var null|string Holds the source.
     */
    private $sSource = null;

    /**
     * @var string What to search for?
     */
    private $sSearchFor = 'all';

    /**
     * @param string $sSearch    IMDb URL or movie title to search for.
     * @param null   $iCache     Custom cache time in minutes.
     * @param string $sSearchFor What to search for?
     *
     * @throws Exception
     */
    public function __construct($sSearch, $iCache = null, $sSearchFor = 'all')
    {
        $this->sRoot = dirname(__FILE__);
        if ( ! is_writable($this->sRoot . '/posters') && ! mkdir($this->sRoot . '/posters')) {
            throw new Exception('The directory “' . $this->sRoot . '/posters” isn’t writable.');
        }
        if ( ! is_writable($this->sRoot . '/cache') && ! mkdir($this->sRoot . '/cache')) {
            throw new Exception('The directory “' . $this->sRoot . '/cache” isn’t writable.');
        }
        if ( ! is_writable($this->sRoot . '/cast') && ! mkdir($this->sRoot . '/cast')) {
            throw new Exception('The directory “' . $this->sRoot . '/cast” isn’t writable.');
        }
        if ( ! function_exists('curl_init')) {
            throw new Exception('You need to enable the PHP cURL extension.');
        }
        if (in_array(
            $sSearchFor,
            [
                'movie',
                'tv',
                'episode',
                'game',
                'documentary',
                'all',
            ]
        )) {
            $this->sSearchFor = $sSearchFor;
        }
        if (true === self::IMDB_DEBUG) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(-1);
            echo '<pre><b>Running:</b> fetchUrl("' . $sSearch . '")</pre>';
        }
        if (null !== $iCache && (int) $iCache > 0) {
            $this->iCache = (int) $iCache;
        }
        
        if (self::IMDB_EXACT_SEARCH) {
            if ($this->fetchUrl($sSearch, self::IMDB_SEARCH_ORIGINAL, true)) {
                return true;
            }
        }

        if ($this->fetchUrl($sSearch, self::IMDB_SEARCH_ORIGINAL)) {
            return true;
        }

        if ($this->fetchUrl($sSearch, !self::IMDB_SEARCH_ORIGINAL)) {
            return true;
        }


    }

    /**
     * @param string $sSearch IMDb URL or movie title to search for.
     *
     * @return bool True on success, false on failure.
     */
    private function fetchUrl($sSearch, $orgSearch = false, $exactSearch = false)
    {
        $sSearch = trim($sSearch);

        // Try to find a valid URL.
        $sId = IMDBHelper::matchRegex($sSearch, self::IMDB_ID, 1);
        if (false !== $sId) {
            $this->iId  = preg_replace('~[\D]~', '', $sId);
            $this->sUrl = 'https://www.imdb.com/title/tt' . $this->iId . '/reference';
            $bSearch    = false;
        } else {
            if (!$orgSearch) {
                switch (strtolower($this->sSearchFor)) {
                    case 'movie':
                        $sParameters = '&title_type=feature';
                        break;
                    case 'tv':
                        $sParameters = '&title_type=tv_movie,tv_series,tv_special,tv_miniseries';
                        break;
                    case 'episode':
                        $sParameters = '&title_type=tv_episode';
                        break;
                    case 'game':
                        $sParameters = '&title_type=video_game';
                        break;
                    case 'documentary':
                        $sParameters = '&title_type=documentary';
                        break;
                    case 'video':
                        $sParameters = '&title_type=video';
                        break;
                    default:
                        $sParameters = '';
                }

                if (preg_match('~([^0-9+])\(?([0-9]{4})\)?~', $sSearch, $fMatch)) {
                    $sParameters .= '&release_date=' . $fMatch[2] . '-01-01,' . $fMatch[2] . '-12-31';
                    $sSearch = preg_replace('~([^0-9+])\(?([0-9]{4})\)?~','', $sSearch);
                }
                
                $this->sUrl = 'https://www.imdb.com/search/title/?title=' . rawurlencode(str_replace(' ', '+', $sSearch)) . $sParameters;               
            } else {
                switch (strtolower($this->sSearchFor)) {
                    case 'movie':
                        $sParameters = '&s=tt&ttype=ft';
                        break;
                    case 'tv':
                        $sParameters = '&s=tt&ttype=tv';
                        break;
                    case 'episode':
                        $sParameters = '&s=tt&ttype=ep';
                        break;
                    case 'game':
                        $sParameters = '&s=tt&ttype=vg';
                        break;
                    default:
                        $sParameters = '&s=tt';
                }
                
                if (preg_match('~([^0-9+])\(?([0-9]{4})\)?~', $sSearch, $fMatch)) {
                    $sYear = $fMatch[2];
                    $sTempSearch = preg_replace('~([^0-9+])\(?([0-9]{4})\)?~','', $sSearch);
                    $sSearch = $sTempSearch . ' (' . $sYear . ')';
                }
                
                if ($exactSearch) {
                    $sParameters .= '&exact=true';
                }
                $this->sUrl = 'https://www.imdb.com/find/?q=' . rawurlencode(str_replace(' ', ' ', $sSearch)) . $sParameters;                
            }
            
            $bSearch    = true;

            // Was this search already performed and cached?
            $sRedirectFile = $this->sRoot . '/cache/' . sha1($this->sUrl) . '.redir';
            if (is_readable($sRedirectFile)) {
                if (self::IMDB_DEBUG) {
                    echo '<pre><b>Using redirect:</b> ' . basename($sRedirectFile) . '</pre>';
                }
                $sRedirect  = file_get_contents($sRedirectFile);
                $this->sUrl = trim($sRedirect);
                $this->iId  = preg_replace('~[\D]~', '', IMDBHelper::matchRegex($sRedirect, self::IMDB_ID, 1));
                $bSearch    = false;
            }
        }

        // Does a cache of this movie exist?
        if (! is_null($this->iId)) {
            $sCacheFile = $this->sRoot . '/cache/' . sha1($this->iId) . '.cache';
            if (is_readable($sCacheFile)) {
                $iDiff = round(abs(time() - filemtime($sCacheFile)) / 60);
                if ($iDiff < $this->iCache) {
                    if (true === self::IMDB_DEBUG) {
                        echo '<pre><b>Using cache:</b> ' . basename($sCacheFile) . '</pre>';
                    }
                    $this->sSource = file_get_contents($sCacheFile);
                    $this->isReady = true;

                    return true;
                }
            }
        }

        // Run cURL on the URL.
        if (true === self::IMDB_DEBUG) {
            echo '<pre><b>Running cURL:</b> ' . $this->sUrl . '</pre>';
        }

        $aCurlInfo = IMDBHelper::runCurl($this->sUrl);
        $sSource   = isset($aCurlInfo['contents']) ? $aCurlInfo['contents'] : false;

        if (false === $sSource) {
            if (true === self::IMDB_DEBUG) {
                echo '<pre><b>cURL error:</b> ' . var_dump($aCurlInfo) . '</pre>';
            }

            return false;
        }

        if (!$orgSearch) {
            // Was the movie found?
            $sMatch = IMDBHelper::matchRegex($sSource, self::IMDB_SEARCH_ADV, 1);
            if (false !== $sMatch) {
                $sUrl = 'https://www.imdb.com/title/' . $sMatch . '/reference';
                if (true === self::IMDB_DEBUG) {
                    echo '<pre><b>New redirect saved:</b> ' . basename($sRedirectFile) . ' => ' . $sUrl . '</pre>';
                }
                file_put_contents($sRedirectFile, $sUrl);
                $this->sSource = null;
                self::fetchUrl($sUrl);

                return true;
            }
            $sMatch = IMDBHelper::matchRegex($sSource, self::IMDB_NOT_FOUND_ADV, 0);
            if (false !== $sMatch) {
                if (true === self::IMDB_DEBUG) {
                    echo '<pre><b>Movie not found:</b> ' . $sSearch . '</pre>';
                }

                return false;
            }
        } else {
            $aReturned = IMDBHelper::matchRegex($sSource, self::IMDB_SEARCH_ORG);

            if ($aReturned) {
                $rData = [];
                $fTempPercent = 0.00;
                $iTempId = "";
                $sYear = 0;

                if (preg_match('~([^0-9+])\(?([0-9]{4})\)?~', $sSearch, $fMatch)) {
                    $sYear = $fMatch[2];
                    $sTempSearch = preg_replace('~([^0-9+])\(?([0-9]{4})\)?~','', $sSearch);
                    if (true === self::IMDB_DEBUG) {
                        echo '<pre><b>YEAR:</b> ' . $sTempSearch . ' =>  ' . $sYear . '</pre>';
                    }
                }

                foreach ($aReturned[1] as $i => $value) {
                    $sId = $aReturned[2][$i];
                    $sTitle = $aReturned[3][$i];
                    $perc = 0.00;
                    $year = 0;

                    if ($sYear === 0) {
                        $sim = similar_text($sSearch, $sTitle, $perc);
                    } else {
                        $sMatch = IMDBHelper::matchRegex($aReturned[1][$i], '~\(?([0-9]{4})\)?~', 1);
                        if (false !== $sMatch) {
                            $year = $sMatch;
                        }

                        if ($sYear != $year) {
                            continue;
                        }

                        $sim = similar_text($sTempSearch, $sTitle, $perc); 
                    }

                    $rData[] = [
                        'id'	=> $sId,
                        'title' => $sTitle,
                        'year' 	=> $year,
                        'match' => floatval($perc)
                    ];
        

                }

                if (sizeof($rData) === 0) {
                    return false;
                }

                if (true === self::IMDB_DEBUG) {
                    foreach ($rData as $sArray) {
                        echo '<pre><b>Found results:</b> ' . $sArray['id'] . ' =>  ' . $sArray['title'] . ' (' . $sArray['match']. '%) </pre>';
                    }
                }
                
                //get highest match of search results
                $matches = array_column($rData, 'match');
                $maxv = max($matches);
        
                $marray = array_filter($rData, function($item) use ($maxv) {
                    return $item['match'] == $maxv;
                });
    
                $marray = reset($marray);

                if (sizeof($marray) > 0) {
                    if (!$exactSearch && round($marray['match'], 0) < self::IMDB_SENSITIVITY) {
                        echo '<pre><b>Bad sensitivity:</b> ' . $marray['id'] . ' =>  ' . $marray['title'] . ' (' . $marray['match']. '%) </pre>';
                        return false;
                    }
                    
                    $sUrl = 'https://www.imdb.com/title/' . $marray['id'] . '/reference';
                    if (true === self::IMDB_DEBUG) {
                        echo '<pre><b>Get best result:</b> ' . $marray['title'] . ' ' . $marray['id'] . ' =>  ' . $marray['match'] . '% </pre>';
                        echo '<pre><b>New redirect saved:</b> ' . basename($sRedirectFile) . ' => ' . $sUrl . '</pre>';
                    }
                    file_put_contents($sRedirectFile, $sUrl);
                    $this->sSource = null;
                    self::fetchUrl($sUrl);

                    return true;                   
                }
            }

            $sMatch = IMDBHelper::matchRegex($sSource, self::IMDB_NOT_FOUND_ORG, 0);
            if (false !== $sMatch) {
                if (true === self::IMDB_DEBUG) {
                    echo '<pre><b>Movie not found:</b> ' . $sSearch . '</pre>';
                }

                return false;
            }

            return false;
        }

        $this->sSource = str_replace(
            [
                "\n",
                "\r\n",
                "\r",
            ],
            '',
            $sSource
        );
        $this->isReady = true;

        // Save cache.
        if (false === $bSearch) {
            if (true === self::IMDB_DEBUG) {
                echo '<pre><b>Cache created:</b> ' . basename($sCacheFile) . '</pre>';
            }
            file_put_contents($sCacheFile, $this->sSource);
        }

        return true;
    }

    /**
     * @return array All data.
     */
    public function getAll()
    {
        $aData = [];
        foreach (get_class_methods(__CLASS__) as $method) {
            if (substr($method, 0, 3) === 'get' && $method !== 'getAll' && $method !== 'getCastImages') {
                $aData[$method] = [
                    'name'  => ltrim($method, 'get'),
                    'value' => $this->{$method}(),
                ];
            }
        }
        array_multisort($aData);

        return $aData;
    }

    /**
     * @return string “Also Known As” or $sNotFound.
     */
    public function getAka()
    {
        if (true === $this->isReady) {
            $aReturn = [];
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_AKA, 1);
            if (false !== $sMatch) {
                $aReturn[] = explode('|', IMDBHelper::cleanString($sMatch));
                
                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * Returns all local names
     *
     * @return string All local names.
     */
    public function getAkas()
    {
        if (true === $this->isReady) {
            // Does a cache of this movie exist?
            $sCacheFile = $this->sRoot . '/cache/' . sha1($this->iId) . '_akas.cache';
            $bUseCache  = false;

            if (is_readable($sCacheFile)) {
                $iDiff = round(abs(time() - filemtime($sCacheFile)) / 60);
                if ($iDiff < $this->iCache || false) {
                    $bUseCache = true;
                }
            }

            if ($bUseCache) {
                $aRawReturn = file_get_contents($sCacheFile);
                $aReturn    = unserialize($aRawReturn);

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            } else {
                $fullAkas  = sprintf('https://www.imdb.com/title/tt%s/releaseinfo/', $this->iId);
                $aCurlInfo = IMDBHelper::runCurl($fullAkas);
                $sSource   = $aCurlInfo['contents'] ?? false;

                if (false === $sSource) {
                    if (true === self::IMDB_DEBUG) {
                        echo '<pre><b>cURL error:</b> ' . var_dump($aCurlInfo) . '</pre>';
                    }

                    return false;
                }

                $aReturned = IMDBHelper::matchRegex($sSource, '~<span class="ipc-metadata-list-item__label"[^>]*>([^<]+)</span>.*?<span class="ipc-metadata-list-item__list-content-item"[^>]*>([^<]+)</span>(?:\s*<span class="ipc-metadata-list-item__list-content-item--subText"[^>]*>\(([^)]+)\)</span>)?~s');

                if ($aReturned) {
                    $aReturn = [];
                    foreach ($aReturned[1] ?? [] as $i => $strName) {
                        if (strpos($strName, '(') === false) {
                            $aReturn[] = [
                                'title'   => IMDBHelper::cleanString($aReturned[2][$i]),
                                'country' => IMDBHelper::cleanString($strName),
                            ];
                        }
                    }

                    file_put_contents($sCacheFile, serialize($aReturn));

                    return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
                }
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * Returns meta score
     *
     * @return string metascore
     * @return string reviews
     */
    public function getMetaScore()
    {
        if (true === $this->isReady) {
            // Does a cache of this movie exist?
            $sCacheFile = $this->sRoot . '/cache/' . sha1($this->iId) . '_metascore.cache';
            $bUseCache  = false;

            if (is_readable($sCacheFile)) {
                $iDiff = round(abs(time() - filemtime($sCacheFile)) / 60);
                if ($iDiff < $this->iCache || false) {
                    $bUseCache = true;
                }
            }

            if ($bUseCache) {
                $aRawReturn = file_get_contents($sCacheFile);
                $aReturn    = unserialize($aRawReturn);

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            } else {
                $fullCritics  = sprintf('https://www.imdb.com/title/tt%s/criticreviews/', $this->iId);
                $aCurlInfo = IMDBHelper::runCurl($fullCritics);
                $sSource   = $aCurlInfo['contents'] ?? false;

                if (false === $sSource) {
                    if (true === self::IMDB_DEBUG) {
                        echo '<pre><b>cURL error:</b> ' . var_dump($aCurlInfo) . '</pre>';
                    }

                    return IMDB::$sNotFound;
                }

                $aReturned = IMDBHelper::matchRegex(
                    $sSource,
                    '~<div class="sc-8c54a7fa-1 gTQbcw">(\d+)</div>.*?(\d+) reviews~s'
                );

                if ($aReturned) {
                    $aReturn = [];
                    $aReturn[] = [
                        'metascore' => isset($aReturned[1][0]) ? IMDBHelper::cleanString($aReturned[1][0]) : '',
                        'reviews' => isset($aReturned[2][0]) ? IMDBHelper::cleanString($aReturned[2][0]) : '',
                    ];

                    file_put_contents($sCacheFile, serialize($aReturn));

                    return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
                }
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * Returns Critic Reviews based on Metascore
     *
     * @return string rating
     * @return string url
     * @return string publisher
     * @return string author
     * @return string review
     */

    public function getMetaCritics()
    {
        if (true === $this->isReady) {
            // Does a cache of this movie exist?
            $sCacheFile = $this->sRoot . '/cache/' . sha1($this->iId) . '_criticreviews.cache';
            $bUseCache  = false;

            if (is_readable($sCacheFile)) {
                $iDiff = round(abs(time() - filemtime($sCacheFile)) / 60);
                if ($iDiff < $this->iCache || false) {
                    $bUseCache = true;
                }
            }

            if ($bUseCache) {
                $aRawReturn = file_get_contents($sCacheFile);
                $aReturn    = unserialize($aRawReturn);

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            } else {
                $fullCritics  = sprintf('https://www.imdb.com/title/tt%s/criticreviews/', $this->iId);
                $aCurlInfo = IMDBHelper::runCurl($fullCritics);
                $sSource   = $aCurlInfo['contents'] ?? false;

                if (false === $sSource) {
                    if (true === self::IMDB_DEBUG) {
                        echo '<pre><b>cURL error:</b> ' . var_dump($aCurlInfo) . '</pre>';
                    }

                    return IMDB::$sNotFound;
                }

                $aReturned = IMDBHelper::matchRegex(
                    $sSource,
                    '~<div class="sc-d8486f96-2 (?:fgepEK|kPhAAe|crRWFG)">(\d+)</div>.*?<span class="sc-d8486f96-5 jyAgZO">(.*?)</span>(?:<a.*?href="(.*?)".*?>)?(.*?)</(?:span|a)>.*?<div>(.*?)</div>~'
                );

                if ($aReturned) {
                    $aReturn = [];
                    foreach ($aReturned[1] as $i => $strScore) {
                            $aReturn[] = [
                                'rating'     => IMDBHelper::cleanString($strScore),
                                'url' => IMDBHelper::cleanString($aReturned[2][$i]),
                                'publisher' => IMDBHelper::cleanString($aReturned[3][$i]),
                                'author' => IMDBHelper::cleanString($aReturned[4][$i]),
                                'review' => IMDBHelper::cleanString($aReturned[5][$i]),
                            ];
                    }

                    file_put_contents($sCacheFile, serialize($aReturn));

                    return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
                }
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }
    
    /**
     * @return string “Aspect Ratio” or $sNotFound.
     */
    public function getAspectRatio()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_ASPECT_RATIO, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @return string The awards of the movie or $sNotFound.
     */
    public function getAwards()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_AWARDS, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param int    $iLimit  How many cast members should be returned?
     * @param bool   $bMore   Add … if there are more cast members than printed.
     * @param string $sTarget Add a target to the links?
     *
     * @return string A list with linked cast members or $sNotFound.
     */
    public function getCastAsUrl($iLimit = 0, $bMore = true, $sTarget = '')
    {
        if (true === $this->isReady) {
            $aMatch  = IMDBHelper::matchRegex($this->sSource, self::IMDB_CAST);
            $aReturn = [];
            if (count($aMatch[2])) {
                foreach ($aMatch[2] as $i => $sName) {
                    if (0 !== $iLimit && $i >= $iLimit) {
                        break;
                    }
                    $aReturn[] = '<a href="https://www.imdb.com/name/' . IMDBHelper::cleanString(
                            $aMatch[1][$i]
                        ) . '/"' . ($sTarget ? ' target="' . $sTarget . '"' : '') . '>' . IMDBHelper::cleanString(
                            $sName
                        ) . '</a>';
                }

                $bHaveMore = ($bMore && (count($aMatch[2]) > $iLimit));

                return IMDBHelper::arrayOutput(
                    $this->bArrayOutput,
                    $this->sSeparator,
                    self::$sNotFound,
                    $aReturn,
                    $bHaveMore
                );
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param int  $iLimit How many cast members should be returned?
     * @param bool $bMore  Add … if there are more cast members than printed.
     *
     * @return string A list with cast members or $sNotFound.
     */
    public function getCast($iLimit = 0, $bMore = true)
    {
        if (true === $this->isReady) {
            $aMatch  = IMDBHelper::matchRegex($this->sSource, self::IMDB_CAST);
            $aReturn = [];
            if (count($aMatch[2])) {
                foreach ($aMatch[2] as $i => $sName) {
                    if (0 !== $iLimit && $i >= $iLimit) {
                        break;
                    }
                    $aReturn[] = IMDBHelper::cleanString($sName);
                }

                $bMore = (0 !== $iLimit && $bMore && (count($aMatch[2]) > $iLimit) ? '…' : '');

                $bHaveMore = ($bMore && (count($aMatch[2]) > $iLimit));

                return IMDBHelper::arrayOutput(
                    $this->bArrayOutput,
                    $this->sSeparator,
                    self::$sNotFound,
                    $aReturn,
                    $bHaveMore
                );
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @param int    $iLimit    How many cast images should be returned?
     * @param bool   $bMore     Add … if there are more cast members than printed.
     * @param string $sSize     small, mid or big cast images
     * @param bool   $bDownload Return URL or Download
     *
     * @return array Array with cast name as key, and image as value.
     */
    public function getCastImages($iLimit = 0, $bMore = true, $sSize = 'small', $bDownload = false)
    {
        if (true === $this->isReady) {
            $aMatch  = IMDBHelper::matchRegex($this->sSource, self::IMDB_CAST_IMAGE);
            $aReturn = [];
            if (count($aMatch[4])) {
                foreach ($aMatch[4] as $i => $sName) {
                    if (0 !== $iLimit && $i >= $iLimit) {
                        break;
                    }
                    $sMatch = $aMatch[2][$i];

                    if ('big' === strtolower($sSize) && false !== strstr($aMatch[2][$i], '@._')) {
                        $sMatch = substr($aMatch[2][$i], 0, strpos($aMatch[2][$i], '@._')) . '@.jpg';
                    } elseif ('mid' === strtolower($sSize) && false !== strstr($aMatch[2][$i], '@._')) {
                        $sMatch = substr($aMatch[2][$i], 0, strpos($aMatch[2][$i], '@._')) . '@._V1_UX214_AL_.jpg';
                    }

                    if (false === $bDownload) {
                        $sMatch = IMDBHelper::cleanString($sMatch);
                    } else {
                        $sLocal = IMDBHelper::saveImageCast($sMatch, $aMatch[3][$i]);
                        if (file_exists(dirname(__FILE__) . '/' . $sLocal)) {
                            $sMatch = $sLocal;
                        } else {
                            //the 'big' image isn't available, try the 'mid' one (vice versa)
                            if ('big' === strtolower($sSize) && false !== strstr($aMatch[2][$i], '@._')) {
                                //trying the 'mid' one
                                $sMatch = substr(
                                        $aMatch[2][$i],
                                        0,
                                        strpos($aMatch[2][$i], '@._')
                                    ) . '@._V1_UX214_AL_.jpg';
                            } else {
                                //trying the 'big' one
                                $sMatch = substr($aMatch[2][$i], 0, strpos($aMatch[2][$i], '@._')) . '@.jpg';
                            }

                            $sLocal = IMDBHelper::saveImageCast($sMatch, $aMatch[3][$i]);
                            if (file_exists(dirname(__FILE__) . '/' . $sLocal)) {
                                $sMatch = $sLocal;
                            } else {
                                $sMatch = IMDBHelper::cleanString($aMatch[2][$i]);
                            }
                        }
                    }

                    $aReturn[IMDBHelper::cleanString($aMatch[4][$i])] = $sMatch;
                }

                $bMore = (0 !== $iLimit && $bMore && (count($aMatch[4]) > $iLimit) ? '…' : '');

                $bHaveMore = ($bMore && (count($aMatch[4]) > $iLimit));

                $aReturn = array_replace(
                    $aReturn,
                    array_fill_keys(
                        array_keys($aReturn, self::$sNotFound),
                        'cast/not-found.jpg'
                    )
                );

                return $aReturn;
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @param int    $iLimit  How many cast members should be returned?
     * @param bool   $bMore   Add … if there are more cast members than
     *                        printed.
     * @param string $sTarget Add a target to the links?
     *
     * @return string A list with linked cast members and their character or
     *                $sNotFound.
     */
    public function getCastAndCharacterAsUrl($iLimit = 0, $bMore = true, $sTarget = '')
    {
        if (true === $this->isReady) {
            $aMatch     = IMDBHelper::matchRegex($this->sSource, self::IMDB_CAST);
            $aMatchChar = IMDBHelper::matchRegex($this->sSource, self::IMDB_CHAR);
            $aReturn    = [];
            if (count($aMatch[2])) {
                foreach ($aMatch[2] as $i => $sName) {
                    if (0 !== $iLimit && $i >= $iLimit) {
                        break;
                    }
                    $sCharRaw = $aMatchChar[1][$i] ?? 'Unknown';
                    $sChar = str_replace(' / ', ' and ', $sCharRaw);
                    $aReturn[] = '<a href="https://www.imdb.com/name/' . IMDBHelper::cleanString(
                            $aMatch[1][$i]
                        ) . '/"' . ($sTarget ? ' target="' . $sTarget . '"' : '') . '>' . IMDBHelper::cleanString(
                            $sName
                        ) . '</a> as ' . IMDBHelper::cleanString($sChar);
                }

                $bHaveMore = ($bMore && (count($aMatch[2]) > $iLimit));

                return IMDBHelper::arrayOutput(
                    $this->bArrayOutput,
                    $this->sSeparator,
                    self::$sNotFound,
                    $aReturn,
                    $bHaveMore
                );
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @param int  $iLimit How many cast members should be returned?
     * @param bool $bMore  Add … if there are more cast members than printed.
     *
     * @return string  A list with cast members and their character or
     *                 $sNotFound.
     */
    public function getCastAndCharacter($iLimit = 0, $bMore = true)
    {
        if (true === $this->isReady) {
            $aMatch     = IMDBHelper::matchRegex($this->sSource, self::IMDB_CAST);
            $aMatchChar = IMDBHelper::matchRegex($this->sSource, self::IMDB_CHAR);
            $aReturn    = [];
            if (count($aMatch[2])) {
                foreach ($aMatch[2] as $i => $sName) {
                    if (0 !== $iLimit && $i >= $iLimit) {
                        break;
                    }
                    $sCharRaw = $aMatchChar[1][$i] ?? 'Unknown';
                    $sChar = str_replace(' / ', ' and ', $sCharRaw);

                    $aReturn[] = IMDBHelper::cleanString($sName) . ' as ' . IMDBHelper::cleanString($sChar);
                }

                $bHaveMore = ($bMore && (count($aMatch[2]) > $iLimit));

                return IMDBHelper::arrayOutput(
                    $this->bArrayOutput,
                    $this->sSeparator,
                    self::$sNotFound,
                    $aReturn,
                    $bHaveMore
                );
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @return string The certification of the movie or $sNotFound.
     */
    public function getCertification()
    {
        if (true === $this->isReady) {
            $aReturn = [];
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_CERTIFICATION, 1);
            if (false !== $sMatch) {
                $aReturn[] = explode('|', IMDBHelper::cleanString($sMatch));
                
                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @return string Color or $sNotFound.
     */
    public function getColor()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_COLOR, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @return string The company producing the movie or $sNotFound.
     */
    public function getCompany()
    {
        if (true === $this->isReady) {
            $sMatch = $this->getCompanyAsUrl();
            if (self::$sNotFound !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param string $sTarget Add a target to the links?
     *
     * @return string The linked company producing the movie or $sNotFound.
     */
    public function getCompanyAsUrl($sTarget = '')
    {
        if (true === $this->isReady) {
            $aMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_COMPANIES);
            $aReturn = [];
            if (isset($aMatch[1][0])) {
                $bMatch = IMDBHelper::matchRegex($aMatch[1][0], self::IMDB_COMPANY);
                if (count($bMatch[2])) {
                    foreach ($bMatch[2] as $i => $sName) {
                        
                        $aReturn[] = '<a href="https://www.imdb.com/company/' . IMDBHelper::cleanString(
                        $bMatch[1][$i]
                        ) . '/"' . ($sTarget ? ' target="' . $sTarget . '"' : '') . '>' . IMDBHelper::cleanString(
                        $sName
                        ) . '</a>';
                        
                    }

                    return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
                }
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @return string A list with countries or $sNotFound.
     */
    public function getCountry()
    {
        if (true === $this->isReady) {
            $sMatch = $this->getCountryAsUrl();
            if (self::$sNotFound !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param string $sTarget Add a target to the links?
     *
     * @return string A list with linked countries or $sNotFound.
     */
    public function getCountryAsUrl($sTarget = '')
    {
        if (true === $this->isReady) {
            $aMatch  = IMDBHelper::matchRegex($this->sSource, self::IMDB_COUNTRY);
            $aReturn = [];
            if (count($aMatch[2])) {
                foreach ($aMatch[2] as $i => $sName) {
                    $aReturn[] = '<a href="https://www.imdb.com/country/' . trim(
                            $aMatch[1][$i]
                        ) . '/"' . ($sTarget ? ' target="' . $sTarget . '"' : '') . '>' . IMDBHelper::cleanString(
                            $sName
                        ) . '</a>';
                }

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @return string A list with the creators or $sNotFound.
     */
    public function getCreator()
    {
        if (true === $this->isReady) {
            $sMatch = $this->getCreatorAsUrl();
            if (self::$sNotFound !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param string $sTarget Add a target to the links?
     *
     * @return string A list with the linked creators or $sNotFound.
     */
    public function getCreatorAsUrl($sTarget = '')
    {
        if (true === $this->isReady) {
            $sMatch  = IMDBHelper::matchRegex($this->sSource, self::IMDB_CREATOR, 1);
            $aMatch  = IMDBHelper::matchRegex($sMatch, self::IMDB_NAME);
            $aReturn = [];
            if (count($aMatch[2])) {
                foreach ($aMatch[2] as $i => $sName) {
                    $aReturn[] = '<a href="https://www.imdb.com/name/' . IMDBHelper::cleanString(
                            $aMatch[1][$i]
                        ) . '/"' . ($sTarget ? ' target="' . $sTarget . '"' : '') . '>' . IMDBHelper::cleanString(
                            $sName
                        ) . '</a>';
                }

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @return string The description of the movie or $sNotFound.
     */
    public function getDescription()
    {
        if (true === $this->isReady) {

            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_SERIESEP_DESC, 1);
            if (false !== $sMatch) {
                if (strpos($sMatch, self::IMDB_NOT_FOUND_DES) === false) {
                    return IMDBHelper::cleanString($sMatch);
                }

            }

            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_SERIES_DESC, 1);
            if (false !== $sMatch) {
                if (strpos($sMatch, self::IMDB_NOT_FOUND_DES) === false) {
                    return IMDBHelper::cleanString($sMatch);
                }
            }

            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_MOVIE_DESC, 1);
            if (false !== $sMatch) {
                if (strpos($sMatch, self::IMDB_NOT_FOUND_DES) === false) {
                    return IMDBHelper::cleanString($sMatch);
                }
            }
            
        }

        return self::$sNotFound;
    }

    /**
     * @return string A list with the directors or $sNotFound.
     */
    public function getDirector()
    {
        if (true === $this->isReady) {
            $sMatch = $this->getDirectorAsUrl();
            if (self::$sNotFound !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param string $sTarget Add a target to the links?
     *
     * @return string A list with the linked directors or $sNotFound.
     */
    public function getDirectorAsUrl($sTarget = '')
    {
        if (true === $this->isReady) {
            $sMatch  = IMDBHelper::matchRegex($this->sSource, self::IMDB_DIRECTOR, 1);
            $aMatch  = IMDBHelper::matchRegex($sMatch, self::IMDB_NAME);
            $aReturn = [];
            if (count($aMatch[2])) {
                foreach ($aMatch[2] as $i => $sName) {
                    $aReturn[] = '<a href="https://www.imdb.com/name/' . IMDBHelper::cleanString(
                            $aMatch[1][$i]
                        ) . '/"' . ($sTarget ? ' target="' . $sTarget . '"' : '') . '>' . IMDBHelper::cleanString(
                            $sName
                        ) . '</a>';
                }

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @param string $sTarget Add a target to the links?
     *
     * @return array A list (name, url, year, country, type) with Distributors or $sNotFound.
     */
    public function getDistributor($iLimit = 0, $bMore = true) 
    {
        if (true === $this->isReady) {
            $aMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_DISTRIBUTOR);
            $aReturn = [];
            if (isset($aMatch[1][0])) {
                $bMatch = IMDBHelper::matchRegex($aMatch[1][0], self::IMDB_DISTRIBUTORS);
                if (count($bMatch[2])) {
                    foreach ($bMatch[2] as $i => $sName) {
                        if (0 !== $iLimit && $i >= $iLimit) {
                            break;
                        }
                        $aReturn[] = [
                            'distributor'     => IMDBHelper::cleanString($sName),
                            'url' => 'https://www.imdb.com/company/' . IMDBHelper::cleanString($bMatch[1][$i]) .'',
                            'year' => IMDBHelper::cleanString($bMatch[3][$i]),
                            'country' => IMDBHelper::cleanString($bMatch[4][$i]),
                            'type' => IMDBHelper::cleanString($bMatch[5][$i]),
                        ];
                    }

                    $bMore = (0 !== $iLimit && $bMore && (count($aMatch[2]) > $iLimit) ? 'â€¦' : '');

                    $bHaveMore = ($bMore && (count($aMatch[2]) > $iLimit));

                    return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn, $bHaveMore);
                
                }
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @param string $sTarget Add a target to the links?
     *
     * @return string A list with the linked distributors or $sNotFound.
     */
    public function getDistributorAsUrl($sTarget = '')
    {
        if (true === $this->isReady) {
            $aMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_DISTRIBUTOR);
            $aReturn = [];
            if (isset($aMatch[1][0])) {
                $bMatch = IMDBHelper::matchRegex($aMatch[1][0], self::IMDB_DISTRIBUTORS);
                if (count($bMatch[2])) {
                    foreach ($bMatch[2] as $i => $sName) {
                        
                        $aReturn[] = '<a href="https://www.imdb.com/company/' . IMDBHelper::cleanString(
                        $bMatch[1][$i]
                        ) . '/"' . ($sTarget ? ' target="' . $sTarget . '"' : '') . '>' . IMDBHelper::cleanString(
                        $sName
                        ) . '</a>';
                        
                    }

                    return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
                }
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }
    
    /**
     * @return string The episode title of the tv show or $sNotFound.
     */
    public function getEpisodeTitle()
    {
        if (true === $this->isReady) {
            if (preg_match('/Episode/i', $this->getType())) {
                $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_TITLE_EP, 1);
                if (false !== $sMatch && "" !== $sMatch) {
                    return IMDBHelper::cleanString($sMatch);
                }  
            }
        }

        return self::$sNotFound;
    }
    
    /**
     * @return string A list with the genres or $sNotFound.
     */
    public function getGenre()
    {
        if (true === $this->isReady) {
            $sMatch = $this->getGenreAsUrl();
            if (self::$sNotFound !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param string $sTarget Add a target to the links?
     *
     * @return string A list with the linked genres or $sNotFound.
     */
    public function getGenreAsUrl($sTarget = '')
    {
        if (true === $this->isReady) {
            $aMatch  = IMDBHelper::matchRegex($this->sSource, self::IMDB_GENRE);
            $aReturn = [];
            if (count($aMatch[2])) {
                foreach (array_unique($aMatch[2]) as $i => $sName) {
                    $aReturn[] = '<a href="https://www.imdb.com/search/title?genres=' . IMDBHelper::cleanString(
                            $aMatch[1][$i]
                        ) . '"' . ($sTarget ? ' target="' . $sTarget . '"' : '') . '>' . IMDBHelper::cleanString(
                            $sName
                        ) . '</a>';
                }

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @return string cumulative worldwide gross or $sNotFound.
     */
    public function getGross()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_GROSS, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @return string A list with the languages or $sNotFound.
     */
    public function getLanguage()
    {
        if (true === $this->isReady) {
            $sMatch = $this->getLanguageAsUrl();
            if (self::$sNotFound !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param string $sTarget Add a target to the links?
     *
     * @return string A list with the linked languages or $sNotFound.
     */
    public function getLanguageAsUrl($sTarget = '')
    {
        if (true === $this->isReady) {
            $aMatch  = IMDBHelper::matchRegex($this->sSource, self::IMDB_LANGUAGE);
            $aReturn = [];
            if (count($aMatch[2])) {
                foreach ($aMatch[2] as $i => $sName) {
                    $aReturn[] = '<a href="https://www.imdb.com/language/' . IMDBHelper::cleanString(
                            $aMatch[1][$i]
                        ) . '"' . ($sTarget ? ' target="' . $sTarget . '"' : '') . '>' . IMDBHelper::cleanString(
                            $sName
                        ) . '</a>';
                }

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @return string A list with the location or $sNotFound.
     */
    public function getLocation()
    {
        if (true === $this->isReady) {
            $sMatch = $this->getLocationAsUrl();
            if (self::$sNotFound !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param string $sTarget Add a target to the links?
     *
     * @return string A list with the linked location or $sNotFound.
     */
    public function getLocationAsUrl($sTarget = '')
    {
        if (true === $this->isReady) {
            $aMatch  = IMDBHelper::matchRegex($this->sSource, self::IMDB_LOCATION);
            $aReturn = [];
            if (count($aMatch[2])) {
                foreach ($aMatch[2] as $i => $sName) {
                    $aReturn[] = '<a href="https://www.imdb.com/search/title?locations=' . IMDBHelper::cleanString(
                            $aMatch[1][$i]
                        ) . '"' . ($sTarget ? ' target="' . $sTarget . '"' : '') . '>' . IMDBHelper::cleanString(
                            $sName
                        ) . '</a>';
                }

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * Returns all locations
     *
     * @return string|array locations
     */
    public function getLocations()
    {
        if (true === $this->isReady) {
            // Does a cache of this movie exist?
            $sCacheFile = $this->sRoot . '/cache/' . sha1($this->iId) . '_locations.cache';
            $bUseCache  = false;

            if (is_readable($sCacheFile)) {
                $iDiff = round(abs(time() - filemtime($sCacheFile)) / 60);
                if ($iDiff < $this->iCache || false) {
                    $bUseCache = true;
                }
            }

            if ($bUseCache) {
                $aRawReturn = file_get_contents($sCacheFile);
                $aReturn    = unserialize($aRawReturn);

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            } else {
                $fullLocations = sprintf('https://www.imdb.com/title/tt%s/locations/', $this->iId);
                $aCurlInfo     = IMDBHelper::runCurl($fullLocations);
                $sSource       = $aCurlInfo['contents'] ?? false;

                if (false === $sSource) {
                    if (true === self::IMDB_DEBUG) {
                        echo '<pre><b>cURL error:</b> ' . var_dump($aCurlInfo) . '</pre>';
                    }

                    return IMDB::$sNotFound;
                }

                $aReturned = IMDBHelper::matchRegex($sSource, self::IMDB_LOCATIONS);

                if ($aReturned) {
                    $aReturn = [];
                    foreach ($aReturned['url'] as $i => $strName) {
                        if (strpos($strName, '(') === false) {
                            $aReturn[] = [
                                'url' => IMDBHelper::cleanString($aReturned['url'][$i]),
                                'location' => IMDBHelper::cleanString($aReturned['location'][$i]),
                                'specification' => IMDBHelper::cleanString($aReturned['specification'][$i]),
                            ];
                        }
                    }

                    file_put_contents($sCacheFile, serialize($aReturn));

                    return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
                }
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @return string The MPAA of the movie or $sNotFound.
     */
    public function getMpaa()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_MPAA, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }
    
    /**
     * @return string A list with the music composers or $sNotFound.
     */
    public function getMusic()
    {
        if (true === $this->isReady) {
            $sMatch = $this->getMusicAsUrl();
            if (self::$sNotFound !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param string $sTarget Add a target to the links?
     *
     * @return string A list with the linked music composers or $sNotFound.
     */
    public function getMusicAsUrl($sTarget = '')
    {
        if (true === $this->isReady) {
            $sMatch  = IMDBHelper::matchRegex($this->sSource, self::IMDB_MUSIC, 1);
            $aMatch  = IMDBHelper::matchRegex($sMatch, self::IMDB_NAME);
            $aReturn = [];
            if (count($aMatch[2])) {
                foreach ($aMatch[2] as $i => $sName) {
                    $aReturn[] = '<a href="https://www.imdb.com/name/' . IMDBHelper::cleanString(
                            $aMatch[1][$i]
                        ) . '/"' . ($sTarget ? ' target="' . $sTarget . '"' : '') . '>' . IMDBHelper::cleanString(
                            $sName
                        ) . '</a>';
                }

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }
    
    /**
     * @param int    $iLimit    How many photo images should be returned?
     * @param bool   $bMore     Add … if there are more cast members than printed.
     * @param string $sSize     small or big images
     *
     * @return string Array with title and url.
     */
    public function getPhotos($iLimit = 0, $bMore = true, $sSize = 'small')
    {
        if (true === $this->isReady) {
            // Does a cache of this movie exist?
            $sCacheFile = $this->sRoot . '/cache/' . sha1($this->iId) . '_gallery.cache';
            $bUseCache  = false;

            if (is_readable($sCacheFile)) {
                $iDiff = round(abs(time() - filemtime($sCacheFile)) / 60);
                if ($iDiff < $this->iCache || false) {
                    $bUseCache = true;
                }
            }

            if ($bUseCache) {
                $aRawReturn = file_get_contents($sCacheFile);
                $aReturn    = unserialize($aRawReturn);
                $anReturn   = [];
                foreach ($aReturn as $i => $sAreturn) {
                    if (0 !== $iLimit && $i >= $iLimit) {
                        break;
                    }
                    $title = $sAreturn['title'];
                    $url = $sAreturn['url'];

                    if ('big' === strtolower($sSize) && false !== strstr($url, '._')) {
                        $url = substr($url, 0, strpos($url, '._')) . '.jpg';
                    }

                    $anReturn[] = [
                        'title'   => $title,
                        'url' => $url,
                    ];
                }
                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $anReturn);

            } else {
                $isPage = true;
                $aReturn = [];
                $page = 1;
                while ($isPage) {
                    $fullPhotos  = sprintf('https://www.imdb.com/title/tt%s/mediaindex?page=%d', $this->iId, $page);
                    $aCurlInfo = IMDBHelper::runCurl($fullPhotos);
                    $sSource   = isset($aCurlInfo['contents']) ? $aCurlInfo['contents'] : false;

                    if (false === $sSource) {
                        if (true === self::IMDB_DEBUG) {
                            echo '<pre><b>cURL error:</b> ' . var_dump($aCurlInfo) . '</pre>';
                        }

                        return IMDB::$sNotFound;
                    }

                    $aReturned = IMDBHelper::matchRegex($sSource, '~title="(.*?)"\s+><img(?:.*)\s+src="(.*?)"\s+\/>~');

                    if ($aReturned) {
                        
                        foreach ($aReturned[1] as $i => $strName) {
                            $aReturn[] = [
                                'title'   => IMDBHelper::cleanString($strName),
                                'url' => IMDBHelper::cleanString($aReturned[2][$i]),
                            ];
                        }
                    }

                    file_put_contents($sCacheFile, serialize($aReturn));
                    if (!preg_match('~class="prevnext"\s>Next~', $sSource)) {
                        $isPage = false;
                    }

                    $page++;          
                }

                $anReturn   = [];
                foreach ($aReturn as $i => $sAreturn) {
                    if (0 !== $iLimit && $i >= $iLimit) {
                        break;
                    }
                    $title = $sAreturn['title'];
                    $url = $sAreturn['url'];

                    if ('big' === strtolower($sSize) && false !== strstr($url, '._')) {
                        $url = substr($url, 0, strpos($url, '._')) . '.jpg';
                    }

                    $anReturn[] = [
                        'title'   => $title,
                        'url' => $url,
                    ];
                }

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $anReturn);
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }
    
    /**
     * @return string A list with the plot keywords or $sNotFound.
     */
    public function getPlotKeywords()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_PLOT_KEYWORDS, 1);
            if (false !== $sMatch) {
                $aReturn = explode('|', IMDBHelper::cleanString($sMatch));

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @param int $iLimit The limit.
     *
     * @return string The plot of the movie or $sNotFound.
     */
    public function getPlot($iLimit = 0)
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_PLOT, 1);
            if (false !== $sMatch) {
                if ($iLimit !== 0) {
                    return IMDBHelper::getShortText(IMDBHelper::cleanString($sMatch), $iLimit);
                }

                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param string $sSize     Small, big, xxs, xs, s poster?
     * @param bool   $bDownload Return URL to the poster or download it?
     *
     * @return bool|string Path to the poster.
     */
    public function getPoster($sSize = 'small', $bDownload = false)
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_POSTER, 1);
            if (false !== $sMatch) {
                if ('big' === strtolower($sSize) && false !== strstr($sMatch, '._')) {
                    $sMatch = substr($sMatch, 0, strpos($sMatch, '._')) . '.jpg';
                }
                if ('xxs' === strtolower($sSize) && false !== strstr($sMatch, '._')) {
                    $sMatch = substr($sMatch, 0, strpos($sMatch, '._')) . '._V1_UY67_CR0,0,45,67_AL_.jpg';
                }
                if ('xs' === strtolower($sSize) && false !== strstr($sMatch, '._')) {
                    $sMatch = substr($sMatch, 0, strpos($sMatch, '._')) . '._V1_UY113_CR0,0,76,113_AL_.jpg';
                }
                if ('s' === strtolower($sSize) && false !== strstr($sMatch, '._')) {
                    $sMatch = substr($sMatch, 0, strpos($sMatch, '._')) . '._V1_UX182_CR0,0,182,268_AL_.jpg';
                }
                if (false === $bDownload) {
                    return IMDBHelper::cleanString($sMatch);
                } else {
                    $sLocal = IMDBHelper::saveImage($sMatch, $this->iId);
                    if (file_exists(dirname(__FILE__) . '/' . $sLocal)) {
                        return $sLocal;
                    } else {
                        return $sMatch;
                    }
                }
            }
        }

        return self::$sNotFound;
    }

    /**
     * @return string The rating of the movie or $sNotFound.
     */
    public function getRating()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_RATING, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @return string The rating count of the movie or $sNotFound.
     */
    public function getRatingCount()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_RATING_COUNT, 1);
            if (false !== $sMatch) {
                return str_replace(',', '', IMDBHelper::cleanString($sMatch));
            }
        }

        return self::$sNotFound;
    }

    /**
     * Release date doesn't contain all the information we need to create a media and
     * we need this function that checks if users can vote target media (if can, it's released).
     *
     * @return  true If the media is released
     */
    public function isReleased()
    {
        $strReturn = $this->getReleaseDate();
        if ($strReturn == self::$sNotFound || $strReturn == 'Not yet released') {
            return false;
        }

        return true;
    }

    /**
     * @return string The release date of the movie or $sNotFound.
     */
    public function getReleaseDate()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_RELEASE_DATE, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * Returns all local names
     *
     * @return string country
     * @return string release date
     */
    public function getReleaseDates()
    {
        if (true === $this->isReady) {
            // Does a cache of this movie exist?
            $sCacheFile = $this->sRoot . '/cache/' . sha1($this->iId) . '_dates.cache';
            $bUseCache  = false;

            if (is_readable($sCacheFile)) {
                $iDiff = round(abs(time() - filemtime($sCacheFile)) / 60);
                if ($iDiff < $this->iCache || false) {
                    $bUseCache = true;
                }
            }

            if ($bUseCache) {
                $aRawReturn = file_get_contents($sCacheFile);
                $aReturn    = unserialize($aRawReturn);

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            } else {
                $fullAkas  = sprintf('https://www.imdb.com/title/tt%s/releaseinfo/', $this->iId);
                $aCurlInfo = IMDBHelper::runCurl($fullAkas);
                $sSource   = $aCurlInfo['contents'] ?? false;

                if (false === $sSource) {
                    if (true === self::IMDB_DEBUG) {
                        echo '<pre><b>cURL error:</b> ' . var_dump($aCurlInfo) . '</pre>';
                    }

                    return IMDB::$sNotFound;
                }

                $aReturned = IMDBHelper::matchRegex(
                    $sSource,
                    '~<a class="ipc-metadata-list-item__label[^>]*>([^<]+)</a>.*?<span class="ipc-metadata-list-item__list-content-item"[^>]*>([^<]+)</span>~s'
                );

                if ($aReturned) {
                    $aReturn = [];
                    foreach ($aReturned[1] as $i => $strName) {
                        if (strpos($strName, '(') === false) {
                            $aReturn[] = [
                                'country'     => IMDBHelper::cleanString($strName),
                                'releasedate' => IMDBHelper::cleanString($aReturned[2][$i]),
                            ];
                        }
                    }

                    file_put_contents($sCacheFile, serialize($aReturn));

                    return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
                }
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @return string The runtime of the movie or $sNotFound.
     */
    public function getRuntime()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_RUNTIME, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @return string A list with the seasons or $sNotFound.
     */
    public function getSeasons()
    {
        if (true === $this->isReady) {
            $sMatch = $this->getSeasonsAsUrl();
            if (self::$sNotFound !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param string $sTarget Add a target to the links?
     *
     * @return string A list with the linked seasons or $sNotFound.
     */
    public function getSeasonsAsUrl($sTarget = '')
    {
        if (true === $this->isReady) {
            $aMatch  = IMDBHelper::matchRegex($this->sSource, self::IMDB_SEASONS);
            $aReturn = [];
            if (count($aMatch[1])) {
                foreach (range(1, max($aMatch[1])) as $i => $sName) {
                    $aReturn[] = '<a href="https://www.imdb.com/title/tt' . $this->iId . '/episodes?season=' . $sName . '"' . ($sTarget ? ' target="' . $sTarget . '"' : '') . '>' . $sName . '</a>';
                }

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }

    /**
     * @return string The sound mix of the movie or $sNotFound.
     */
    public function getSoundMix()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_SOUND_MIX, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @return string The tagline of the movie or $sNotFound.
     */
    public function getTagline()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_TAGLINE, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param bool $bForceLocal Try to return the original name of the movie.
     *
     * @return string The title of the movie or $sNotFound.
     */
    public function getTitle($bForceLocal = false)
    {
        if (true === $this->isReady) {
            if (true === $bForceLocal) {
                $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_TITLE_ORIG, 1);
                if (false !== $sMatch && "" !== $sMatch) {
                    return IMDBHelper::cleanString($sMatch);
                }
            }

            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_TITLE, 1);
            $sMatch = preg_replace('~\(\d{4}\)$~Ui', '', $sMatch);
            if (false !== $sMatch && "" !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @return string returns the given position at top-250 or $sNotFound.
     */
    public function getTop250()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_TOP250, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param bool $bEmbed Link to player directly?
     *
     * @return string The URL to the trailer of the movie or $sNotFound.
     */
    public function getTrailerAsUrl($bEmbed = false)
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_TRAILER, 1);
            if (false !== $sMatch) {
                if ($bEmbed) {
                    $sUrl = 'https://www.imdb.com/video/imdb/' . $sMatch . '/imdb/embed';
                } else {
                    $sUrl = 'https://www.imdb.com/video/' . $sMatch;
                }

                return IMDBHelper::cleanString($sUrl);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @return array Array with season, episode, title, rating, votes, airdate, plot, id.
     */
    public function getTVInfo()
    {
        if (true === $this->isReady) {
            // Does a cache of this movie exist?
            $sCacheFile = $this->sRoot . '/cache/' . sha1($this->iId) . '_tv.cache';
            $bUseCache  = false;

            if (is_readable($sCacheFile)) {
                $iDiff = round(abs(time() - filemtime($sCacheFile)) / 60);
                if ($iDiff < $this->iCache || false) {
                    $bUseCache = true;
                }
            }

            if ($bUseCache) {
                $aRawReturn = file_get_contents($sCacheFile);
                $aReturn    = unserialize($aRawReturn);
                $anReturn   = [];
                foreach ($aReturn as $i => $sAreturn) {
                    $season = $sAreturn['season'];
                    $episode = $sAreturn['episode'];
                    $title = $sAreturn['title'];
                    $rating = $sAreturn['rating'];
                    $votes = $sAreturn['votes'];
                    $airdate = $sAreturn['airdate'];
                    $plot = $sAreturn['plot'];
                    $id = $sAreturn['id'];

                    $anReturn[] = [
                        'season'    => $season,
                        'episode'   => $episode,
                        'title'     => $title,
                        'rating'    => $rating,
                        'votes'     => $votes,
                        'airdate'   => $airdate,
                        'plot'      => $plot,
                        'id'        => $id
                    ];
                }
                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $anReturn);

            } else {
                $isPage = true;
                $aReturn = [];
                $page = 1;
                while ($isPage) {
                    $fullEpisodes  = sprintf('https://www.imdb.com/title/tt%s/episodes/?season=%d', $this->iId, $page);

                    $aCurlInfo = IMDBHelper::runCurl($fullEpisodes);
                    $sSource   = isset($aCurlInfo['contents']) ? $aCurlInfo['contents'] : false;

                    if (false === $sSource) {
                        if (true === self::IMDB_DEBUG) {
                            echo '<pre><b>cURL error:</b> ' . var_dump($aCurlInfo) . '</pre>';
                        }
                        return IMDB::$sNotFound;
                    }

                    $aSeasonsLinks = IMDBHelper::matchRegex($sSource, '~tab-season-entry" href="/title/tt\d+/episodes/\?season=(\d)~s');
                    $aFoundSeasons = [];
                    if ($aSeasonsLinks) {
                        foreach ($aSeasonsLinks[1] as $i => $aSeasonNumber) {
                            $aFoundSeasons[] = $aSeasonNumber;
                        }

                    }

                    if (!in_array($page, $aFoundSeasons)) {
                        break;
                    }

                    $aSplit = IMDBHelper::matchRegex($sSource, '~<article class=.+?episode-item-wrapper(.+?)ipc-rating-star--rate">Rate</span>~s');
                    
                    if ($aSplit) {
                        foreach ($aSplit[1] as $i => $text) {
                            
                            # Set default values
                            $dEpisode = self::$sNotFound;
                            $dTitle = self::$sNotFound;
                            $dRating = self::$sNotFound;
                            $dVotes = self::$sNotFound;
                            $dAirdate = self::$sNotFound;
                            $dPlot = self::$sNotFound;
                            $dId = self::$sNotFound;

                            # Find values
                            $fId = IMDBHelper::matchRegex($aSplit[1][$i], '~h4.+/title/(tt\d+)~s');
                            $fEpisode = IMDBHelper::matchRegex($aSplit[1][$i], '~ref_=ttep_ep_(\d+)~s');
                            $fTitle = IMDBHelper::matchRegex($aSplit[1][$i], '~S\d+\.E\d+ ∙ (.+?)<\/div>~s');
                            $fAirdate = IMDBHelper::matchRegex($aSplit[1][$i], '~<span class="sc-ccd6e31b-10 fVspdm">(.+?)<\/span>~s');
                            $fPlot = IMDBHelper::matchRegex($aSplit[1][$i], '~"ipc-html-content-inner-div" role="presentation">(.+?)<\/div>~s');
                            $fRaiting = IMDBHelper::matchRegex($aSplit[1][$i], '~IMDb rating: (\d\.\d)~s');
                            $fVotes = IMDBHelper::matchRegex($aSplit[1][$i], '~voteCount.+?-->(.+?)<~s');

                            # Update values if not empty
                            if (!empty($fId[1][0])) {
                                $dId = IMDBHelper::cleanString($fId[1][0]);
                            }

                            if (!empty($fEpisode[1][0])) {
                                $dEpisode = IMDBHelper::cleanString($fEpisode[1][0]);
                            }

                            if (!empty($fTitle[1][0])) {
                                $dTitle = IMDBHelper::cleanString($fTitle[1][0]);
                            }

                            if (!empty($fRaiting[1][0])) {
                                $dRating = IMDBHelper::cleanString($fRaiting[1][0]);
                            }

                            if (!empty($fVotes[1][0])) {
                                $dVotes = IMDBHelper::cleanString($fVotes[1][0]);
                            }

                            if (!empty($fAirdate[1][0])) {
                                $dAirdate = IMDBHelper::cleanString($fAirdate[1][0]);
                            }

                            if (!empty($fPlot[1][0])) {
                                $dPlot = IMDBHelper::cleanString($fPlot[1][0]);
                            }


                            $aReturn[] = [
                                'season'  => $page,
                                'episode' => $dEpisode,
                                'title'   => $dTitle,
                                'rating'  => $dRating,
                                'votes'   => $dVotes,
                                'airdate' => $dAirdate,
                                'plot'    => $dPlot,
                                'id'      => $dId,
                            ];

                        }
                    }

                    file_put_contents($sCacheFile, serialize($aReturn));

                    $page++;
                }

                $anReturn   = [];
                foreach ($aReturn as $i => $sAreturn) {
                    $season = $sAreturn['season'];
                    $episode = $sAreturn['episode'];
                    $title = $sAreturn['title'];
                    $rating = $sAreturn['rating'];
                    $votes = $sAreturn['votes'];
                    $airdate = $sAreturn['airdate'];
                    $plot = $sAreturn['plot'];
                    $id = $sAreturn['id'];

                    $anReturn[] = [
                        'season'    => $season,
                        'episode'   => $episode,
                        'title'     => $title,
                        'rating'    => $rating,
                        'votes'     => $votes,
                        'airdate'   => $airdate,
                        'plot'      => $plot,
                        'id'        => $id
                    ];
                }

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $anReturn);
            }
        }

        return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound);
    }


    /**
     *
     * @return string type of the title or $sNotFound.
     */
    public function getType()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_TYPE, 1);
            if (false !== $sMatch && "" !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }   
        }

        return self::$sNotFound;
    }
    
    /**
     * @return string The IMDb URL.
     */
    public function getUrl()
    {
        if (true === $this->isReady) {
            return IMDBHelper::cleanString(str_replace('reference', '', $this->sUrl));
        }

        return self::$sNotFound;
    }

    /**
     * @return string The user review of the movie or $sNotFound.
     */
    public function getUserReview()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_USER_REVIEW, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @return string The votes of the movie or $sNotFound.
     */
    public function getVotes()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_VOTES, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @return string A list with the writers or $sNotFound.
     */
    public function getWriter()
    {
        if (true === $this->isReady) {
            $sMatch = $this->getWriterAsUrl();
            if (self::$sNotFound !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @param string $sTarget Add a target to the links?
     *
     * @return string A list with the linked writers or $sNotFound.
     */
    public function getWriterAsUrl($sTarget = '')
    {
        if (true === $this->isReady) {
            $sMatch  = IMDBHelper::matchRegex($this->sSource, self::IMDB_WRITER, 1);
            $aMatch  = IMDBHelper::matchRegex($sMatch, self::IMDB_NAME);
            $aReturn = [];
            if (count($aMatch[2])) {
                foreach ($aMatch[2] as $i => $sName) {
                    $aReturn[] = '<a href="https://www.imdb.com/name/' . IMDBHelper::cleanString(
                            $aMatch[1][$i]
                        ) . '/"' . ($sTarget ? ' target="' . $sTarget . '"' : '') . '>' . IMDBHelper::cleanString(
                            $sName
                        ) . '</a>';
                }

                return IMDBHelper::arrayOutput($this->bArrayOutput, $this->sSeparator, self::$sNotFound, $aReturn);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @return string The year of the movie or $sNotFound.
     */
    public function getYear()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_YEAR, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }

    /**
     * @return string The budget of the movie or $sNotFound.
     */
    public function getBudget()
    {
        if (true === $this->isReady) {
            $sMatch = IMDBHelper::matchRegex($this->sSource, self::IMDB_BUDGET, 1);
            if (false !== $sMatch) {
                return IMDBHelper::cleanString($sMatch);
            }
        }

        return self::$sNotFound;
    }
}

class IMDBHelper extends IMDB
{
    /**
     * Regular expression helper.
     *
     * @param string $sContent The content to search in.
     * @param string $sPattern The regular expression.
     * @param null   $iIndex   The index to return.
     *
     * @return bool   If no match was found.
     * @return string If one match was found.
     * @return array  If more than one match was found.
     */
    public static function matchRegex($sContent, $sPattern, $iIndex = null)
    {
        preg_match_all($sPattern, $sContent ?? '', $aMatches);
        if ($aMatches === false) {
            return false;
        }
        if ($iIndex !== null && is_int($iIndex)) {
            if (isset($aMatches[$iIndex][0])) {
                return $aMatches[$iIndex][0];
            }

            return false;
        }

        return $aMatches;
    }

    /**
     * Preferred output in responses with multiple elements
     *
     * @param bool   $bArrayOutput Native array or string with separators.
     * @param string $sSeparator   String separator.
     * @param string $sNotFound    Not found text.
     * @param array  $aReturn      Original input.
     * @param bool   $bHaveMore    Have more elements indicator.
     *
     * @return string|array Multiple results separated by selected separator string, or enclosed into native array.
     */
    public static function arrayOutput($bArrayOutput, $sSeparator, $sNotFound, $aReturn = null, $bHaveMore = false)
    {
        if ($aReturn === null) {
            return $bArrayOutput ? [] : $sNotFound;
        }

        if ($bArrayOutput) {
return $bHaveMore ? array_merge($aReturn, ['…']) : $aReturn;

        }

function processValue($value, $sNotFound = '—', $sSeparator = ', ') {
    if (is_array($value)) {
        $filtered = array_filter($value, function($v) {
            return $v !== '' && $v !== null;
        });

        if (empty($filtered)) {
            return $sNotFound;
        }

        $mapped = array_map(function($v) use ($sNotFound) {
            return $v ?: $sNotFound;
        }, $value);

        return implode($sSeparator, $mapped);
    }

    if ($value === '' || $value === null) {
        return $sNotFound;
    }

    return (string)$value;
}


        $result = implode($sSeparator, array_map($processValue, $aReturn));
        return $bHaveMore ? $result . '…' : $result;
    }

    /**
     * @param string $sInput Input (eg. HTML).
     *
     * @return string Cleaned string.
     */
    public static function cleanString($sInput)
    {
        $aSearch  = [
            'Full summary &raquo;',
            'Full synopsis &raquo;',
            'Add summary &raquo;',
            'Add synopsis &raquo;',
            'See more &raquo;',
            'See why on IMDbPro.',
            "\n",
            "\r",
        ];
        $aReplace = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        $sInput   = str_replace('</li>', ' | ', $sInput ?? '');
        $sInput   = strip_tags($sInput ?? '');
        $sInput   = str_replace('&nbsp;', ' ', $sInput ?? '');
        $sInput   = str_replace($aSearch, $aReplace, $sInput ?? '');
        $sInput   = html_entity_decode($sInput ?? '', ENT_QUOTES | ENT_HTML5);
        $sInput   = preg_replace('/\s+/', ' ', $sInput ?? '');
        $sInput   = trim($sInput ?? '');
        $sInput   = rtrim($sInput ?? '', ' |');

        return ($sInput ? trim($sInput ?? '') : self::$sNotFound);
    }

    /**
     * @param string $sText   The long text.
     * @param int    $iLength The maximum length of the text.
     *
     * @return string The shortened text.
     */
    public static function getShortText($sText, $iLength = 100)
    {
        if (mb_strlen($sText) <= $iLength) {
            return $sText;
        }

        list($sShort) = explode("\n", wordwrap($sText, $iLength - 1));

        if (substr($sShort, -1) !== '.') {
            return $sShort . '…';
        }

        return $sShort;
    }

    /**
     * @param string $sUrl The URL to the image to download.
     * @param int    $iId  The ID of the movie.
     *
     * @return string Local path.
     */
    public static function saveImage($sUrl, $iId)
    {
        if (preg_match('~title_addposter.jpg|imdb-share-logo.png~', $sUrl)) {
            return 'posters/not-found.jpg';
        }

        $sFilename = dirname(__FILE__) . '/posters/' . $iId . '.jpg';
        if (file_exists($sFilename)) {
            return 'posters/' . $iId . '.jpg';
        }

        $aCurlInfo = self::runCurl($sUrl, true);
        $sData     = $aCurlInfo['contents'];
        if (false === $sData) {
            return 'posters/not-found.jpg';
        }

        $oFile = fopen($sFilename, 'x');
        fwrite($oFile, $sData);
        fclose($oFile);

        return 'posters/' . $iId . '.jpg';
    }

    /**
     * @param string $sUrl      The URL to fetch.
     * @param bool   $bDownload Download?
     *
     * @return bool|mixed Array on success, false on failure.
     */
    public static function runCurl($sUrl, $bDownload = false)
    {
        $oCurl = curl_init($sUrl);
        curl_setopt_array(
            $oCurl,
            [
                CURLOPT_BINARYTRANSFER => ($bDownload ? true : false),
                CURLOPT_CONNECTTIMEOUT => self::IMDB_TIMEOUT,
                CURLOPT_ENCODING       => '',
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_FRESH_CONNECT  => 0,
                CURLOPT_HEADER         => ($bDownload ? false : true),
                CURLOPT_HTTPHEADER     => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Charset: utf-8, iso-8859-1;q=0.5',
                    'Accept-Language: ' . self::IMDB_LANG,
                ],
                CURLOPT_REFERER        => 'https://www.imdb.com',
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_TIMEOUT        => self::IMDB_TIMEOUT,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0',
                CURLOPT_VERBOSE        => 0,
            ]
        );
        $sOutput   = curl_exec($oCurl);
        $aCurlInfo = curl_getinfo($oCurl);
        curl_close($oCurl);
        $aCurlInfo['contents'] = $sOutput;

        if (200 !== $aCurlInfo['http_code'] && 302 !== $aCurlInfo['http_code']) {
            if (true === self::IMDB_DEBUG) {
                echo '<pre><b>cURL returned wrong HTTP code “' . $aCurlInfo['http_code'] . '” for “' . $aCurlInfo['url'] . '”, aborting.</b></pre>';
            }

            return false;
        }

        return $aCurlInfo;
    }

    /**
     * @param string $sUrl The URL to the image to download.
     * @param int    $cId  The cast ID of the actor.
     *
     * @return string Local path.
     */
    public static function saveImageCast($sUrl, $cId)
    {
        if ( ! preg_match('~http~', $sUrl)) {
            return 'cast/not-found.jpg';
        }

        $sFilename = dirname(__FILE__) . '/cast/' . $cId . '.jpg';
        if (file_exists($sFilename)) {
            return 'cast/' . $cId . '.jpg';
        }

        $aCurlInfo = self::runCurl($sUrl, true);
        $sData     = $aCurlInfo['contents'];
        if (false === $sData) {
            return 'cast/not-found.jpg';
        }

        $oFile = fopen($sFilename, 'x');
        fwrite($oFile, $sData);
        fclose($oFile);

        return 'cast/' . $cId . '.jpg';
    }
}
