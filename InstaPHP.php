<?php
/**
 * Instagram API for PHP
 * @author Yevhen Matasar <matasar.ei@gmail.com>
 * @copyright C-Format, 2015
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version 20150225
 */
class InstaPHP {
    
    /**
     * @var string UserName 
     */
    private $_username;
    
    /**
     * @var type Access token
     */
    private $_accessToken;
    
    /**
     * @var int User ID
     */
    private $_userID;
    
    /**
     * @var int Cache time in seconds
     */
    private $_cacheTime;
    
    /**
     * @var string Cache path
     */
    private $_cachePath;
    
    /**
     * @var string Instagram API URL
     */
    private $_APIURL = 'https://api.instagram.com/v1/';
    
    /**
     * @param type $username Instagram username
     * @param type $accessToken OAuth access token
     * @param type $cacheTime Cache time in minutes
     * @param type $cachePath Cache path
     */
    function __construct($username, $accessToken, $cacheTime = 10, $cachePath = './cache/') {
        if (!$username && !$accessToken) {
            trigger_error("Wrong username or token");
        }
        $this->_username = (string)$username;
        $this->_accessToken = (string)$accessToken;
        $this->setCachePath($cachePath);
        $this->setCacheTime($cacheTime);
    }
    
    /**
     * Cache path setter
     * @param string $path
     */
    function setCachePath($path) {
        !$path && $this->_cachePath = null;
        if (is_writable($path)) {
            $this->_cachePath = $path;
        } else {
            trigger_error("Cache path does not exist or not writable");
            $this->_cachePath = null;
        }
    }
    
    /**
     * Set cache time
     * @param type $time Time in minutes
     */
    function setCacheTime($time) {
        $this->_cacheTime = 60 * (int)$time;
    }
    
    /**
     * Get user ID
     * @return type
     */
    function getUserID() {
        if (!$this->_userID) {
            $data = $this->_query('users/search', array('q'=>$this->_username));
            $this->_userID = $data[0]->id;
        }
        return $this->_userID;
    }
    
    /**
     * Get basic information about a user.
     * @param type $userName
     * @return type
     */
    function getUserInfo($userName = null) {
        !$userName && $userName = $this->_username;
        $data = $this->_query('users/search', array('q' => $userName));
        return $data[0];
    }
    
    /**
     * Search for a user by username
     * @param type $userName
     * @return array All matches
     */
    function searchUser($userName) {
        return $this->_query('users/search', array('q' => $userName));
    }
    
    /**
     * Get media by current user
     * @param int $limit Items limit
     * @return array Media
     */
    function getUserMedia($limit = 20) {
        return $this->_query("users/{$this->getUserID()}/media/recent", array(
            'count' => $limit
        ));
    }
    
    /**
     * See the authenticated user's list of media they've liked
     * @param int $limit Items limit
     * @return array Items
     */
    function getUserLiked($limit = 20) {
        return $this->_query("users/self/media/liked", array(
            'count' => $limit
        ));
    }
    
    /**
     * Get the list of users this user follows.
     * @return array Users list
     */
    function getUserFollows() {
        return $this->_query("users/{$this->getUserID()}/follows");
    }
    
    /**
     * Get the list of users this user is followed by
     * @return array Users list
     */
    function getUserFollowers() {
        return $this->_query("users/{$this->getUserID()}/followed-by");
    }
    
    /**
     * Get information about a media object
     * @param mixed $uniq Item ID or shortcode
     * @return stdClass Item info
     */
    function getMedia($uniq) {
        if (is_numeric($uniq)) {
            return $this->_query("media/{$uniq}");
        } else {
            return $this->_query("media/shortcode/{$uniq}");
        }
    }
    
    /**
     * Get a list of recent comments on a media object.
     * @param type $mediaID Item ID
     * @return array Comments
     */
    function getComments($mediaID) {
        return $this->_query("media/{$mediaID}/comments");
    }
    
    /**
     * Get a list of users who have liked this media.
     * @param type $mediaID Item ID
     * @return array Users
     */
    function getLikes($mediaID) {
        return $this->_query("/media/{$mediaID}/likes");
    }
    
    /**
     * Get media by tag name
     * @param type $tag Tag name
     * @param type $limit Items limit
     * @return array Media
     */
    function getTagMedia($tag, $limit = 20) {
        return $this->_query("tags/{$tag}/media/recent", array(
            'count' => $limit
        ));
    }
    
    /**
     * Search for media by tag name
     * @param type $tag Tag name
     * @return array All found tags info
     */
    function searchTag($tag) {
        return $this->_query('tags/search', array('q' => $tag));
    }
    
    /**
     * Get tag info
     * @param type $tag Tag name
     * @return stdClass Tag info
     */
    function getTagInfo($tag) {
        return $this->_query("tags/{$tag}");
    }
    
    /**
     * Send and process query
     * @param string $path Path (e.g. 'users/search');
     * @param array $params query params (optional)
     * @return stdClass Result
     */
    private function _query($path, $params = array()) {
        $params['access_token'] = $this->_accessToken;
        $url = "{$this->_APIURL}{$path}?" . http_build_query($params,'','&');
        
        $cacheKey = md5($url);
        $filePath = "{$this->_cachePath}{$cacheKey}.tmp";
        $cache = file_exists($filePath);
        
        if ($cache && (filemtime($filePath) + $this->_cacheTime > time())) {
            $result = file_get_contents($filePath);
        } else {
            $result = file_get_contents($url);
            
            //If path writable, save cache
            if ($result && $this->_cachePath) {
                file_put_contents($filePath, $result);
            } elseif ($cache) {
                //get old data if error
                $result = file_get_contents($filePath);
            }
        }
        $result = json_decode($result);
        
        if ($result && $result->meta->code === 200) {
            return $result->data;
        } else {
            trigger_error(isset($result->meta) ?
                "{$result->meta->error_type} ({$result->meta->code}): {$result->meta->error_message}":
                "Cannot process query");
            return null;
        }
    }
    
    /**
     * Remove all cache files
     */
    function purgeCaches() {
        if ($this->_cachePath) {
            $files = glob("{$this->_cachePath}*.tmp");
            foreach($files as $file) {
                unlink($file);
            }
            return true;
        }
        return false;
    }
}
