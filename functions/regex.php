<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Regex
 */

//Regex functions we need
class regex
{
    //Simple function
    public function match($regex, $string, $i = null, $ii = 0)
    {
        preg_match_all($regex, $string, $matches);

        if ($i != null)
        {
            return (isset($matches[$i][$ii]) ? $matches[$i][$ii] : false);
        }
        else
        {
            return $matches;
        }
    }

    //Main
    public function main($regex, $string, $i = null, $ii = 0)
    {
        $return["year"] = "~(\b(19\d{2}|20[01]\d)\b)~i";
        $return["yify"] = "~<li(?:.*)data-id=\"(\d+)\"(?:.*)reply-rating(?:.*)>(\d+)</span>(?:.*)flag-(?:.*)>(?:.*)<span>(.*)</span>(?:.*)<a(?:.*)href=\"(.*)\"(?:.*)download~Ui";
        $return["osId"] = "~servOC\((\d+)~i";

        return self::match($return[$regex], $string, $i, $ii);
    }

    //IMDB
    public function imdb($regex, $string, $i = null, $ii = 0)
    {
        $return["title"] = "~property='og:title' content=\"(.*)(?:\s*)\((?:.*)\)\"~Ui";
        $return["originalTitle"] = "~<span class=\"title-extra\" itemprop=\"name\">(?:\s*)\"(.*)\"~Ui";
        $return["plot"] = "~Storyline</h2>(?:\s*)<div class=\"inline canwrap\" itemprop=\"description\">(?:\s*)<p>(?:\s)(.*)(?:<em|<\/p>|<\/div>)~Ui";
        $return["runtime"] = "~<time itemprop=\"duration\" datetime=\"(?:.*)\"(?:\s*)>(?:\s*)(.*)(?:min|</time>)~Uis";
        $return["rating"] = "~<span itemprop=\"ratingValue\">(.*)</span>~Ui";
        $return["releaseDate"] = "~Release Date:</h4>(.*)(?:\s*)(?:\(|<span|<\/div>)~Ui";
        $return["genre"] = "~href=\"/genre/(.*)(?:\?.*)\"(?:\s*)>(.*)</a>~Ui";
        $return["poster"] = "~\"(?:\s*)src=\"(.*)\"(?:\s*)itemprop=\"image\" \/>~Ui";

        return self::match($return[$regex], $string, $i, $ii);
    }
	
	//Torrentz
	public function torrentz($regex, $string, $i = null, $ii = 0)
    {
        $return["maxEnd"] = "~<a(?:.*)(?:\d+)\">(\d+)</a>~Ui";
        $return["movie"] = "~<dl><dt><a href=\"\/([a-f0-9]*)\"(?:\s*)>(.*)<\/a>~Uis";	
        $return["cleanTitle"] = "~(.*\b((19|20)\d{2})\b)~i";	
        $return["illegal"] = "~\b(dvdscrrip|dvdscr|dvd|hdcam|cam|hdts|ts|avchd|xvid|divx)\b~i";	

        return self::match($return[$regex], $string, $i, $ii);
    }
}

?>