<?

require_once('TwitterAPIExchange.php');

/**
* Twitter-SocialAPISearch
*
* PHP version 5.3.10
*
* @package  SocialAPISearch
* @author Nicolai Pefaur at PluralAndPartners <nicolai@zunboy.com>
* @license Apache V2 License
* @link https://github.com/Pluralandpartners/SocialAPISearch/
* @API Rate Limiting https://dev.twitter.com/docs/rate-limiting/1.1
*/

class TwitterAPISearch
{
	private $hashtag;
	private $since_id;
	private $feeds = array();
	private $twitter;

	public function __construct(array $settings, array $query)
    {
     	if (!isset($settings['oauth_access_token'])
            || !isset($settings['oauth_access_token_secret'])
            || !isset($settings['consumer_key'])
            || !isset($settings['consumer_secret']))
        {
            throw new Exception('Make sure you are passing in the correct parameters');
        }
        
        if (!isset($query['hashtag'])
            || !isset($query['since_id']))
        {
            throw new Exception('Make sure you are passing in the correct parameters');
        }
        
        $this->hashtag = $query['hashtag'];
        $this->since_id = $query['since_id'];
		    $this->twitter = new TwitterAPIExchange($settings);
  }

	public function getFeeds()
	{
		if (!isset($this->feeds))
        {
            throw new Exception('Empty feeds');
        }
        
    $url = 'https://api.twitter.com/1.1/search/tweets.json';
		$requestMethod = 'GET';
		$getfield = '?since_id='.$this->since_id.'&q='.$this->hashtag.'&count=100&include_entities=1';
		
		do
		{ 
			$response =  $this->twitter->setGetfield($getfield)
			                              ->buildOauth($url, $requestMethod)
			                              ->performRequest();                 
			$response = json_decode($response, true);
			if(isset($response{'search_metadata'}{'next_results'})) {
				$getfield = $response{'search_metadata'}{'next_results'}.'&since_id='.$this->since_id;
			}
			else
			{
				$getfield = '';
			}
			
			$statuses = $response{'statuses'};
			
			for ($i = 0; $i < count($statuses); $i++) 
			{
				if($this->postValidate($statuses, $i))
				{
					$this->feeds['created_at'][] = $this->setDateTime($statuses[$i]{'created_at'});
					$this->feeds['user'][] = $statuses[$i]{'user'}{'name'};
					$this->feeds['uid'][] = $statuses[$i]{'user'}{'id'};
					$this->feeds['profile_image_url'][] = $statuses[$i]{'user'}{'profile_image_url'};
					$this->feeds['text'][] = $statuses[$i]{'text'};
					$this->feeds['since_id'][] = $statuses[$i]{'id'};
				 }
			}
		
		}while($getfield != '');
		
		return $this->feeds;
	}
	
	public function getFeedsLenght()
	{
		if (!is_null($this->feeds))
        {
            throw new Exception('Empty feeds');
        }
		return count($this->feeds);
	}
	
	
	private function postValidate($statuses, $i)
	{
		if(isset($statuses[$i]{'created_at'}) 
		   && isset($statuses[$i]{'text'}) 
		   && isset($statuses[$i]{'user'}{'name'}) 
		   && isset($statuses[$i]{'user'}{'profile_image_url'})
		   && isset($statuses[$i]{'user'}{'id'})) 
		{
			return true;
		}
		else return false;
	}
	
	private function setDateTime($tw_date)
	{
		return date( 'Y-m-d H:i:s', strtotime($tw_date));
	}

}

?>
