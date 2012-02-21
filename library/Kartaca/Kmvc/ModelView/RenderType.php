<?php

namespace Kartaca\Kmvc\ModelView;

class RenderType
{
    /**
     * To render nothing but just to output the data.
     */
    const NONE = 0;
    /**
     * To render only the content without the layout
     */
    const PARTIAL = 1;
    /**
     * To render full content with layout and html
     */
    const FULL = 2;
    
    /**
     * Outputs a Json File from the arguments added to the view...
     */
    const JSON = 3;
    
    /**
     * This is a special render mode designed for crawlers to get a parsed HTML
     */
    const CRAWLER = 4;
}