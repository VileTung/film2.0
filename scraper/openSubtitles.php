<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Open subtitles
 */

class openSubtitles
{
    //Mark if we failed or not
    private $failed = false;

    public function retrieve($id, $filename, $language = "nl")
    {
        global $logging;

        //Message
        $logging->info("OpenSubtitles.org (" . $id . " - " . $language . ")");

        //Default value
        $this->failed = false;

        //Default, Dutch
        self::subtitle($id, $filename, $language);

        if ($this->failed)
        {
            //Message
            $logging->warning("Dutch failed, let's try English");

            //Second try, English
            self::subtitle($id, $filename, "en");
        }
    }

    private function subtitle($id, $filename, $language)
    {
        global $logging;

        try
        {
            //Determine what language
            if ($language == "nl")
            {
                $l1 = "nl";
                $l2 = "dut";
            }
            else
            {
                $l1 = "en";
                $l2 = "eng";
            }

            //Get page
            list($state, $content) = cURL("http://www.opensubtitles.org/nl/search/sublanguageid-" . $l2 . "/imdbid-" . $id);

            if (!$state)
            {
                throw new Exception("Failed retrieving OpenSubtitles.org");
            }

            //Regex
            $regex = new regex();

            $dom = new DOMDocument();
            @$dom->loadHTML($content);

            //Default
            $result = false;

            //Find all "tr" tags
            foreach ($dom->getElementsByTagName("tr") as $tr)
            {
                //Get onclick with "servOC"
                if (strpos($tr->getAttribute("onclick"), "servOC") !== false)
                {
                    //Get ID, we need it to download the subtitle
                    $subId = $regex->main("osId", $tr->getAttribute("onclick"), 1);

                    //Escape ( and )
                    $name = preg_replace("~(\(|\))~", "\\\\$1", $tr->getAttribute("onclick"));

                    //Get filename, for comparison
                    $test = strtolower($regex->match("~" . $name . "(?s:.*)<br />(.*)<br />~Ui", $content, 1));

                    //Remove extension from the given filename
                    $info = pathinfo($filename);
                    $file = strtolower($info["filename"]);

                    //Compare
                    similar_text($test, $file, $percent);

                    //If confident enough!
                    if ($percent > 90)
                    {
                        $result = $subId;
                    }
                }
            }

            //Error
            if (!$result)
            {
                $this->failed = true;
                throw new Exception("No subtitle(s) found!");
            }

            //Subtitle URL
            $url = "http://dl.opensubtitles.org/nl/download/sub/" . $result;

            //Retrieve subtitle
            $getSubtitle = new subtitle($id);
            $getSubtitle->saveSubtitle($url, $l1);
        }
        //Error reporting
        catch (exception $e)
        {
            $logging->error($e->getMessage());
        }
    }
}

?>