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

    //Read settings
    private $settings;

    public function retrieve($id, $filename, $language = "nl")
    {
        global $logging;

        //Message
        $logging->info("OpenSubtitles.org (" . $id . " - " . $language . ")");

        //Settings
        $this->settings = new settings();

        //Calc datetime
        $dateTime = (int)$this->settings->get("osDateTime") + (60 * 60 * 24);
        $now = time();	

        //Reset dateTime
        if ($now > $dateTime)
        {
            //Message
            $logging->info("Reset dateTime");

            $this->settings->set("osCount", "0");
            $this->settings->set("osDateTime", time());
            $this->settings->set("osEnabled", "true");
        }

        //Allowed?
        if ($this->settings->get("osEnabled"))
        {
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
        //Disabled
        else
        {
            //Locked till
            $locked = date("d-m-Y H:i:s", $dateTime);

            //Message
            $logging->debug("OpenSubtitles is disabled (" . $locked . ")");
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

            //Get count
            $osCount = $this->settings->get("osCount");

            if ($osCount >= 200)
            {
                $this->settings->set("osEnabled", "false");

                throw new Exception("Limit reached (" . $osCount . ")!");
            }
            //Update count
            else
            {
                $osCount++;

                $this->settings->set("osCount", $osCount);

                //Message
                $logging->debug("Subtitle count: " . $osCount);
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