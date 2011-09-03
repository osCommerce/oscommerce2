<?php
/* $Id: phplot.php 993 2011-07-30 21:12:03Z lbayuk $ */
/*
 * PHPLOT Version 5.5.0
 *
 * A PHP class for creating scientific and business charts
 * Visit http://sourceforge.net/projects/phplot/
 * for PHPlot documentation, downloads, and discussions.
 * ---------------------------------------------------------------------
 * Copyright (C) 1998-2011 Afan Ottenheimer
 *
 * This is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation;
 * version 2.1 of the License.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this software; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 * ---------------------------------------------------------------------
 *
 * Co-author and maintainer (2003-2005)
 * Miguel de Benito Delgado <nonick AT vodafone DOT es>
 *
 * Maintainer (2006-present)
 * <lbayuk AT users DOT sourceforge DOT net>
 *
 * Requires PHP 5.2.x or later.
 */

class PHPlot
{
    const version = '5.5.0';

    /* Declare class variables which are initialized to static values. Many more class variables
     * are used, defined as needed, but are unset by default.
     * All these are declared as public. While it is tempting to make them private or protected, this
     * is avoided for two reasons. First, it will break existing code, since all member variables
     * were public in PHP4 and who knows what internal variables people used. Second, it makes
     * testing harder and less effective. Nevertheless, your code should not modify these.
     */

    public $is_inline = FALSE;             // FALSE = Sends headers, TRUE = sends just raw image data
    public $browser_cache = FALSE;         // FALSE = Sends headers for browser to not cache the image,
                                           // (only if is_inline = FALSE also)
    public $print_image = TRUE;            // DrawGraph calls PrintImage. See SetPrintImage

    public $safe_margin = 5;               // Extra margin used in several places, in pixels

    public $x_axis_position = '';          // X axis position in Y world coordinates, blank for default.
    public $y_axis_position = '';          // Y axis position in X world coordinates, blank for default.

    public $xscale_type = 'linear';        // linear, log
    public $yscale_type = 'linear';

//Fonts
    public $use_ttf  = FALSE;              // Use True Type Fonts by default?
    public $ttf_path = '.';                // Default path to look in for TT Fonts.
    // public $default_ttfont;             // Initialized in GetDefaultTTFont
    public $line_spacing = 4;              // Controls line spacing of multi-line labels

    // Label angles: 0 or 90 degrees for fixed fonts, any for TTF
    public $x_label_angle = 0;             // For X tick labels
    // public $x_data_label_angle;         // For X data labels; defaults to x_label_angle - see CheckLabels()
    public $y_label_angle = 0;             // For Y tick labels
    public $y_data_label_angle = 0;        // For Y data labels

//Formats
    public $file_format = 'png';
    public $output_file = '';              // For output to a file instead of stdout

//Data
    public $data_type = 'text-data';       // Structure of the data array
    public $plot_type = 'linepoints';      // See $plots[] below

    public $label_scale_position = 0.5;    // Shifts data labels in pie charts. 1 = top, 0 = bottom
    public $group_frac_width = 0.7;        // Bars use this fraction (0 to 1) of a group's space
    public $bar_extra_space = 0.5;         // Number of extra bar's worth of space in a group
    public $bar_width_adjust = 1;          // 1 = bars of normal width, must be > 0

// Titles
    public $title_txt = '';

    public $x_title_txt = '';
    public $x_title_pos = 'none';          // plotdown, plotup, both, none

    public $y_title_txt = '';
    public $y_title_pos = 'none';          // plotleft, plotright, both, none

//Labels
    // There are two types of labels in PHPlot:
    //    Tick labels: Follow the grid, next to ticks in axis.
    //                 Are drawn at grid drawing time, by DrawXTicks() and DrawYTicks()
    //    Data labels: Follow the data points, and can be placed on the axis or the plot (x/y)
    //                 Are drawn at graph plotting time, by Draw*DataLabel(), called by DrawLines(), etc.
    //                 DrawXDataLabel() also draws vertical lines to data points, depending on
    //                 draw_x_data_label_lines.
    // Tick Labels
    // Tick and Data label positions are not initialized, because PHPlot needs to tell if they
    // defaulted or are set by the user. See CheckLabels() for details. The variables and
    // effective defaults are shown here in comments (but CheckLabels adjusts the defaults).
    // public $x_tick_label_pos = 'plotdown';     // X tick label position
    // public $y_tick_label_pos = 'plotleft';     // Y tick label position
    // public $x_data_label_pos = 'plotdown';     // X data label position
    // public $y_data_label_pos = 'none';         // Y data label position

    public $draw_x_data_label_lines = FALSE;   // Draw a line from the data point to the axis?

    // Label format controls: (for tick, data and plot labels)
    // Unset by default, these array members are used as needed for 'x' (x tick labels), 'xd' (x data
    // labels), 'y' (y tick labels), and 'yd' (y data labels).
    //    type, precision, prefix, suffix, time_format, printf_format, custom_callback, custom_arg.
    // These replace the former: x_label_type, x_time_format, x_precision (similar for y), data_units_text.
    public $label_format = array('x' => array(), 'xd' => array(), 'y' => array(), 'yd' => array());
    // data_units_text is retained for backward compatibility, because there was never a function
    // to set it. Use the 'suffix' argument to Set[XY]LabelType instead.
    public $data_units_text = '';              // Units text for 'data' labels (i.e: '�', '$', etc.)

// Legend
    public $legend = '';                       // An array with legend titles
    // Other legend_* variables are set as needed, unset for default values.

//Ticks
    public $x_tick_length = 5;                 // tick length in pixels for upper/lower axis
    public $y_tick_length = 5;                 // tick length in pixels for left/right axis

    public $x_tick_cross = 3;                  // ticks cross x axis this many pixels
    public $y_tick_cross = 3;                  // ticks cross y axis this many pixels

    public $x_tick_pos = 'plotdown';           // plotdown, plotup, both, xaxis, none
    public $y_tick_pos = 'plotleft';           // plotright, plotleft, both, yaxis, none

    public $num_x_ticks = '';
    public $num_y_ticks = '';

    public $x_tick_inc = '';                   // Set num_x_ticks or x_tick_inc, not both.
    public $y_tick_inc = '';                   // Set num_y_ticks or y_tick_inc, not both.

    public $skip_top_tick = FALSE;
    public $skip_bottom_tick = FALSE;
    public $skip_left_tick = FALSE;
    public $skip_right_tick = FALSE;

//Grid Formatting
    // public $draw_x_grid = FALSE;            // Default is False except for swapped data type
    // public $draw_y_grid = TRUE;             // Default is True except for swapped data type

    public $dashed_grid = TRUE;
    public $grid_at_foreground = FALSE;        // Chooses whether to draw the grid below or above the graph

//Colors and styles       (all colors can be array (R,G,B) or named color)
    public $color_array = 'small';             // 'small', 'large' or array (define your own colors)
                                            // See rgb.inc.php and SetRGBArray()
    public $default_colors = array(       // The default colors for data and error bars
        'SkyBlue', 'green', 'orange', 'blue', 'red', 'DarkGreen', 'purple', 'peru',
        'cyan', 'salmon', 'SlateBlue', 'YellowGreen', 'magenta', 'aquamarine1', 'gold', 'violet');

    // See SetDefaultStyles() for default colors for PHPlot elements.

    public $line_widths = 1;                  // single value or array
    public $line_styles = array('solid', 'solid', 'dashed');   // single value or array
    public $dashed_style = '2-4';              // colored dots-transparent dots

    public $point_sizes = array(6);            // Array of sizes for points. See CheckPointParams()
    public $point_shapes = array(              // Array of point shapes. See SetPointShapes() and DrawDot()
          'diamond', 'dot', 'delta', 'home', 'yield', 'box', 'circle', 'up', 'down', 'cross'
       );

    public $error_bar_size = 5;                // right and left size of tee
    public $error_bar_shape = 'tee';           // 'tee' or 'line'
    public $error_bar_line_width = 1;          // single value (or array TODO)

    public $plot_border_type = 'sides';        // left, right, top, bottom, sides, none, full; or array
    public $image_border_type = 'none';        // 'raised', 'plain', 'none'
    // public $image_border_width;             // NULL, 0, or unset for default. Default depends on type.

    public $shading = 5;                       // 0 for no shading, > 0 is size of shadows in pixels

    public $draw_plot_area_background = FALSE;
    public $draw_broken_lines = FALSE;          // Tells not to draw lines for missing Y data.

//Miscellaneous
    public $callbacks = array(                  // Valid callback reasons (see SetCallBack)
        'draw_setup' => NULL,
        'draw_image_background' => NULL,
        'draw_plotarea_background' => NULL,
        'draw_titles' => NULL,
        'draw_axes' => NULL,
        'draw_graph' => NULL,
        'draw_border' => NULL,
        'draw_legend' => NULL,
        'draw_all' => NULL,
        'data_color' => NULL,
        'debug_textbox' => NULL,  // For testing/debugging text box alignment
        'debug_scale' => NULL,    // For testing/debugging scale setup
    );

    // Defined plot types static array:
    // Array key is the plot type. (Upper case letters are not allowed due to CheckOption)
    // Value is an array with these keys:
    //   draw_method (required) : Class method to call to draw the plot.
    //   draw_arg : Optional array of arguments to pass to draw_method.
    //   draw_axes : If FALSE, do not draw X/Y axis lines, labels, ticks, grid, titles.
    //   abs_vals, sum_vals : Data array processing flags. See FindDataLimits().
    static protected $plots = array(
        'area' => array(
            'draw_method' => 'DrawArea',
            'abs_vals' => TRUE,
        ),
        'bars' => array(
            'draw_method' => 'DrawBars',
        ),
        'bubbles' => array(
            'draw_method' => 'DrawBubbles',
        ),
        'candlesticks' => array(
            'draw_method' => 'DrawOHLC',
            'draw_arg' => array(TRUE, FALSE), // Draw candlesticks, only fill if "closed down"
        ),
        'candlesticks2' => array(
            'draw_method' => 'DrawOHLC',
            'draw_arg' => array(TRUE, TRUE), // Draw candlesticks, fill always
        ),
        'linepoints' => array(
            'draw_method' => 'DrawLinePoints',
        ),
        'lines' => array(
            'draw_method' => 'DrawLines',
        ),
        'ohlc' => array(
            'draw_method' => 'DrawOHLC',
            'draw_arg' => array(FALSE), // Don't draw candlesticks
        ),
        'pie' => array(
            'draw_method' => 'DrawPieChart',
            'draw_axes' => FALSE,
            'abs_vals' => TRUE,
        ),
        'points' => array(
            'draw_method' => 'DrawDots',
        ),
        'squared' => array(
            'draw_method' => 'DrawSquared',
        ),
        'stackedarea' => array(
            'draw_method' => 'DrawArea',
            'draw_arg' => array(TRUE), // Tells DrawArea to draw stacked area plot
            'sum_vals' => TRUE,
            'abs_vals' => TRUE,
        ),
        'stackedbars' => array(
            'draw_method' => 'DrawStackedBars',
            'sum_vals' => TRUE,
        ),
        'thinbarline' => array(
            'draw_method' => 'DrawThinBarLines',
        ),
    );

//////////////////////////////////////////////////////
//BEGIN CODE
//////////////////////////////////////////////////////

    /*
     * Constructor: Setup img resource, colors and size of the image, and font sizes.
     *
     *   $which_width : Image width in pixels.
     *   $which_height : Image height in pixels.
     *   $which_output_file : Filename for output.
     *   $which_input_file : Path to a file to be used as background.
     */
    function PHPlot($which_width=600, $which_height=400, $which_output_file=NULL, $which_input_file=NULL)
    {
        $this->SetRGBArray($this->color_array);

        if ($which_output_file)
            $this->SetOutputFile($which_output_file);

        if ($which_input_file) {
            $this->SetInputFile($which_input_file);
        } else {
            $this->image_width = $which_width;
            $this->image_height = $which_height;

            $this->img = ImageCreate($this->image_width, $this->image_height);
            if (! $this->img)
                return $this->PrintError('PHPlot(): Could not create image resource.');
        }

        $this->SetDefaultStyles();
        $this->SetDefaultFonts();
    }

    /*
     * Reads an image file. Stores width and height, and returns the image
     * resource. On error, calls PrintError and returns False.
     * This is used by the constructor via SetInputFile, and by tile_img().
     */
    protected function GetImage($image_filename, &$width, &$height)
    {
        $error = '';
        $size = getimagesize($image_filename);
        if (!$size) {
            $error = "Unable to query image file $image_filename";
        } else {
            $image_type = $size[2];
            switch ($image_type) {
            case IMAGETYPE_GIF:
                $img = @ ImageCreateFromGIF ($image_filename);
                break;
            case IMAGETYPE_PNG:
                $img = @ ImageCreateFromPNG ($image_filename);
                break;
            case IMAGETYPE_JPEG:
                $img = @ ImageCreateFromJPEG ($image_filename);
                break;
            default:
                $error = "Unknown image type ($image_type) for image file $image_filename";
                break;
            }
        }
        if (empty($error) && !$img) {
            // getimagesize is OK, but GD won't read it. Maybe unsupported format.
            $error = "Failed to read image file $image_filename";
        }
        if (!empty($error)) {
            return $this->PrintError("GetImage(): $error");
        }
        $width = $size[0];
        $height = $size[1];
        return $img;
    }

    /*
     * Selects an input file to be used as background for the whole graph.
     * This resets the graph size to the image's size.
     * Note: This is used by the constructor. It is deprecated for direct use.
     */
    function SetInputFile($which_input_file)
    {
        $im = $this->GetImage($which_input_file, $this->image_width, $this->image_height);
        if (!$im)
            return FALSE;  // GetImage already produced an error message.

        // Deallocate any resources previously allocated
        if (isset($this->img))
            imagedestroy($this->img);

        $this->img = $im;

        // Do not overwrite the input file with the background color.
        $this->done['background'] = TRUE;

        return TRUE;
    }

/////////////////////////////////////////////
//////////////                         COLORS
/////////////////////////////////////////////

    /*
     * Allocate a GD color index for a color specified by a 4 component array.
     * When a color is requested, it is parsed and checked by SetRGBColor, and then saved as an array
     * of (R,G,B,A) components. At graph drawing time, this function is used to allocate the color.
     *   $color : The color specification as a 4 component array: R, G, B, A.
     * Returns: A GD color index that can be used when drawing.
     */
    protected function GetColorIndex($color)
    {
        list($r, $g, $b, $a) = $color;
        return imagecolorresolvealpha($this->img, $r, $g, $b, $a);
    }

    /*
     * Allocate an array of GD color indexes for an array of color specifications.
     * This is used for the data_colors array, for example.
     *  $color_array : Array of color specifications, each an array of R,G,B,A components.
     *     This must use 0-based sequential integer indexes.
     *  $max_colors : Limit color allocation to no more than this.
     * Returns an array of GD color indexes.
     */
    protected function GetColorIndexArray($color_array, $max_colors)
    {
        $n = min(count($color_array), $max_colors);
        $result = array();
        for ($i = 0; $i < $n; $i++)
            $result[] = $this->GetColorIndex($color_array[$i]);
        return $result;
    }

    /*
     * Allocate an array of GD color indexes for darker shades of an array of color specifications.
     *  $color_array : Array of color specifications, each an array of R,G,B,A components.
     *  $max_colors : Limit color allocation to this many colors from the array.
     * Returns an array of GD color indexes.
     */
    protected function GetDarkColorIndexArray($color_array, $max_colors)
    {
        $n = min(count($color_array), $max_colors);
        $result = array();
        for ($i = 0; $i < $n; $i++)
            $result[] = $this->GetDarkColorIndex($color_array[$i]);
        return $result;
    }

    /*
     * Allocate a GD color index for a darker shade of a color specified by a 4 component array.
     * See notes for GetColorIndex() above.
     *   $color : The color specification as a 4 component array: R, G, B, A.
     * Returns: A GD color index that can be used when drawing.
     */
    protected function GetDarkColorIndex($color)
    {
        list ($r, $g, $b, $a) = $color;
        $r = max(0, $r - 0x30);
        $g = max(0, $g - 0x30);
        $b = max(0, $b - 0x30);
        return imagecolorresolvealpha($this->img, $r, $g, $b, $a);
    }

    /*
     * Sets/reverts all colors and styles to their defaults.
     */
    protected function SetDefaultStyles()
    {
        $this->SetDefaultDashedStyle($this->dashed_style);
        $this->SetImageBorderColor(array(194, 194, 194));
        $this->SetPlotBgColor('white');
        $this->SetBackgroundColor('white');
        $this->SetTextColor('black');
        $this->SetGridColor('black');
        $this->SetLightGridColor('gray');
        $this->SetTickColor('black');
        $this->SetTitleColor('black');
        // These functions set up the default colors when called without parameters
        $this->SetDataColors();
        $this->SetErrorBarColors();
        $this->SetDataBorderColors();
        return TRUE;
    }

    /*
     * Set the image background color to $which_color.
     */
    function SetBackgroundColor($which_color)
    {
        return (bool)($this->bg_color = $this->SetRGBColor($which_color));
    }

    /*
     * Set the plot area background color (if enabled) to $which_color.
     */
    function SetPlotBgColor($which_color)
    {
        return (bool)($this->plot_bg_color = $this->SetRGBColor($which_color));
    }

    /*
     * Set the color of the titles (main, X, and Y) to $which_color.
     * See also SetXTitleColor and SetYTitleColor.
     */
    function SetTitleColor($which_color)
    {
        return (bool)($this->title_color = $this->SetRGBColor($which_color));
    }

    /*
     * Set the color of the X title to $which_color.
     * This overrides the color set with SetTitleColor.
     */
    function SetXTitleColor($which_color)
    {
        return (bool)($this->x_title_color = $this->SetRGBColor($which_color));
    }

    /*
     * Set the color of the Y title to $which_color.
     * This overrides the color set with SetTitleColor.
     */
    function SetYTitleColor($which_color)
    {
        return (bool)($this->y_title_color = $this->SetRGBColor($which_color));
    }

    /*
     * Set the color of the axis tick marks to $which_color.
     */
    function SetTickColor($which_color)
    {
        return (bool)($this->tick_color = $this->SetRGBColor($which_color));
    }

    /*
     * Deprecated. Use SetTitleColor()
     */
    function SetLabelColor($which_color)
    {
        return $this->SetTitleColor($which_color);
    }

    /*
     * Set the general text color (tick and data labels, legend, etc) to $which_color.
     */
    function SetTextColor($which_color)
    {
        return (bool)($this->text_color = $this->SetRGBColor($which_color));
    }

    /*
     * Set the X and Y grid colors to $which_color. Also sets the data label line color.
     */
    function SetLightGridColor($which_color)
    {
        return (bool)($this->light_grid_color = $this->SetRGBColor($which_color));
    }

    /*
     * Set the color used for the X and Y axis, plot border, legend border to $which_color.
     * Note: This has nothing to do with the grid, and we don't recall where this name came from.
     */
    function SetGridColor($which_color)
    {
        return (bool)($this->grid_color = $this->SetRGBColor($which_color));
    }

    /*
     * Set the color used for the image border to $which_color.
     */
    function SetImageBorderColor($which_color)
    {
        return (bool)($this->i_border = $this->SetRGBColor($which_color));
    }

    /*
     * Designate color $which_color to be transparent, if supported by the image format.
     */
    function SetTransparentColor($which_color)
    {
        return (bool)($this->transparent_color = $this->SetRGBColor($which_color));
    }

    /*
     * Sets the array of colors to be used. It can be user defined, a small predefined one
     * or a large one included from 'rgb.inc.php'.
     *
     *    $which_color_array : A color array, or 'small' or 'large'.
     * Color arrays map color names into arrays of R, G, B and optionally A values.
     */
    function SetRGBArray($which_color_array)
    {
        if (is_array($which_color_array)) {           // User defined array
            $this->rgb_array = $which_color_array;
        } elseif ($which_color_array == 'small') {      // Small predefined color array
            $this->rgb_array = array(
                'white'          => array(255, 255, 255),
                'snow'           => array(255, 250, 250),
                'PeachPuff'      => array(255, 218, 185),
                'ivory'          => array(255, 255, 240),
                'lavender'       => array(230, 230, 250),
                'black'          => array(  0,   0,   0),
                'DimGrey'        => array(105, 105, 105),
                'gray'           => array(190, 190, 190),
                'grey'           => array(190, 190, 190),
                'navy'           => array(  0,   0, 128),
                'SlateBlue'      => array(106,  90, 205),
                'blue'           => array(  0,   0, 255),
                'SkyBlue'        => array(135, 206, 235),
                'cyan'           => array(  0, 255, 255),
                'DarkGreen'      => array(  0, 100,   0),
                'green'          => array(  0, 255,   0),
                'YellowGreen'    => array(154, 205,  50),
                'yellow'         => array(255, 255,   0),
                'orange'         => array(255, 165,   0),
                'gold'           => array(255, 215,   0),
                'peru'           => array(205, 133,  63),
                'beige'          => array(245, 245, 220),
                'wheat'          => array(245, 222, 179),
                'tan'            => array(210, 180, 140),
                'brown'          => array(165,  42,  42),
                'salmon'         => array(250, 128, 114),
                'red'            => array(255,   0,   0),
                'pink'           => array(255, 192, 203),
                'maroon'         => array(176,  48,  96),
                'magenta'        => array(255,   0, 255),
                'violet'         => array(238, 130, 238),
                'plum'           => array(221, 160, 221),
                'orchid'         => array(218, 112, 214),
                'purple'         => array(160,  32, 240),
                'azure1'         => array(240, 255, 255),
                'aquamarine1'    => array(127, 255, 212)
                );
        } elseif ($which_color_array == 'large')  {    // Large color array
            if (!@include('rgb.inc.php')) {
                return $this->PrintError("SetRGBArray(): Large color map could not be loaded\n"
                                       . "from 'rgb.inc.php'.");
            }
            $this->rgb_array = $ColorArray;
        } else {                                        // Default to black and white only.
            $this->rgb_array = array('white' => array(255, 255, 255), 'black' => array(0, 0, 0));
        }

        return TRUE;
    }

    /*
     * Parse a color description and return the color component values.
     * Arguments:
     *   $color_asked : The desired color description, in one of these forms:
     *       Component notation: array(R, G, B) or array(R, G, B, A) with each
     *          in the range described below for the return value.
     *          Examples: (255,255,0)  (204,0,0,30)
     *       Hex notation: "#RRGGBB" or "#RRGGBBAA" where each pair is a 2 digit hex number.
     *          Examples: #FF00FF (magenta)   #0000FF40 (Blue with alpha=64/127)
     *       Named color in the current colormap, with optional suffix ":alpha" for alpha value.
     *          Examples:  blue   red:60  yellow:20
     *   $alpha : optional default alpha value. This is applied to the color if it doesn't
     *       already have an alpha value. If not supplied, colors are opaque (alpha=0) by default.
     *
     * Returns an array describing a color as (R, G, B, Alpha).
     * R, G, and B are integers 0-255, and Alpha is 0 (opaque) to 127 (transparent).
     * Note: This function should be considered 'protected', and is not documented for public use.
     */
    function SetRGBColor($color_asked, $alpha = 0)
    {
        if (empty($color_asked)) {
            $ret_val = array(0, 0, 0);

        } elseif (is_array($color_asked) && (($n = count($color_asked)) == 3 || $n == 4) ) {
            // Already an array of 3 or 4 elements:
            $ret_val = $color_asked;

        } elseif (preg_match('/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})?$/i',
                             $color_asked, $ss)) {
            // #RRGGBB or #RRGGBBAA notation:
            $ret_val = array(hexdec($ss[1]), hexdec($ss[2]), hexdec($ss[3]));
            if (isset($ss[4])) $ret_val[] = hexdec($ss[4]);

        } elseif (isset($this->rgb_array[$color_asked])) {
            // Color by name:
            $ret_val = $this->rgb_array[$color_asked];

        } elseif (preg_match('/(.+):([\d]+)$/', $color_asked, $ss)
                  && isset($this->rgb_array[$ss[1]])) {
            // Color by name with ":alpha" suffix, alpha is a decimal number:
            $ret_val = $this->rgb_array[$ss[1]];
            $ret_val[3] = (int)$ss[2];

        } else {
            return $this->PrintError("SetRGBColor(): Color '$color_asked' is not valid.");
        }

        // Append alpha if not already provided for:
        if (count($ret_val) == 3)
            $ret_val[] = $alpha;
        return $ret_val;
    }

    /*
     * Sets the colors for the data, with optional default alpha value
     * Cases are:
     *    SetDataColors(array(...))  : Use the supplied array as the color map.
     *    SetDataColors(colorname)   : Use an array of just colorname as the color map.
     *    SetDataColors() or SetDataColors(NULL) : Load default color map if no color map is already set.
     *    SetDataColors('') or SetDataColors(False) : Load default color map (even if one is already set).
     *  $which_border is passed to SetDataBorderColors, for backward compatibility.
     *  $alpha is a default Alpha to apply to all data colors that do not have alpha.
     *    The default for this is NULL, not 0, so we can tell if it was defaulted. But the effective
     *    default value is 0 (opaque).
     */
    function SetDataColors($which_data = NULL, $which_border = NULL, $alpha = NULL)
    {
        if (is_array($which_data)) {
            $colors = $which_data;  // Use supplied array
        } elseif (!empty($which_data)) {
            $colors = array($which_data);  // Use supplied single color
        } elseif (empty($this->data_colors) || !is_null($which_data)) {
            $colors = $this->default_colors;  // Use default color array
        } else {
            // which_data is NULL or missing and a color array is already set.
            // The existing color array is left alone, except that if $alpha is
            // given this will replace the alpha value of each existing color.
            // This makes SetDataColors(NULL, NULL, $alpha) work.
            if (isset($alpha)) {
                $n_colors = count($this->data_colors);
                for ($i = 0; $i < $n_colors; $i++) {
                    $this->data_colors[$i][3] = $alpha; // Component 3 = alpha value
                }
            }
            // No need to reparse the colors or anything else.
            return TRUE;
        }

        if (!isset($alpha))
            $alpha = 0; // Actual default is opaque colors.

        // Check each color and convert to array (r,g,b,a) form.
        // Use the $alpha argument as a default for the alpha value of each color.
        $this->data_colors = array();
        foreach ($colors as $color) {
            $color_array = $this->SetRGBColor($color, $alpha);
            if (!$color_array) return FALSE; // SetRGBColor already did an error message.
            $this->data_colors[] = $color_array;
        }

        // For past compatibility:
        return $this->SetDataBorderColors($which_border);
    }

    /*
     * Set the colors for the bars and stacked bars outlines.
     * Argument usage is similar to SetDataColors(), except the default is just black.
     */
    function SetDataBorderColors($which_br = NULL)
    {
        if (is_array($which_br)) {
            $colors = $which_br; // Use supplied array
        } elseif (!empty($which_br)) {
            $colors = array($which_br);  // Use supplied single color
        } elseif (empty($this->data_border_colors) || !is_null($which_br)) {
            $colors = array('black'); // Use default
        } else {
            return TRUE; // Do nothing: which_br is NULL or missing and a color array is already set.
        }

        // Check each color and convert to array (r,g,b,a) form.
        $this->data_border_colors = array();
        foreach ($colors as $color) {
            $color_array = $this->SetRGBColor($color);
            if (!$color_array) return FALSE; // SetRGBColor already did an error message.
            $this->data_border_colors[] = $color_array;
        }
        return TRUE;
    }

    /*
     * Sets the colors for the data error bars.
     * Argument usage is the same as SetDataColors().
     */
    function SetErrorBarColors($which_err = NULL)
    {
        if (is_array($which_err)) {
            $colors = $which_err;  // Use supplied array
        } elseif (!empty($which_err)) {
            $colors = array($which_err);  // Use supplied single color
        } elseif (empty($this->error_bar_colors) || !is_null($which_err)) {
            $colors = $this->default_colors;  // Use default color array
        } else {
            return TRUE; // Do nothing: which_err is NULL or missing and a color array is already set.
        }

        // Check each color and convert to array (r,g,b,a) form.
        $this->error_bar_colors = array();
        foreach ($colors as $color) {
            $color_array = $this->SetRGBColor($color);
            if (!$color_array) return FALSE; // SetRGBColor already did an error message.
            $this->error_bar_colors[] = $color_array;
        }
        return TRUE;
    }

    /*
     * Sets the default dashed line style.
     *   $which_style : A string specifying the dashed line style, as alternating numbers
     *         of the length (in pixels) of lines and spaces, separated by dashes.
     *   For example: '2-3-1-2' means 2 dots of color, 3 transparent, 1 color, then 2 transparent.
     *   This builds a string which will evaluate to an array of integers. Each colored dot
     *   is  '$which_ndxcol' and each transparent dot is 'IMG_COLOR_TRANSPARENT'. When SetDashedStyle()
     *   eval's this with $which_ndxcol set, the result is a GD line style array.
     */
    function SetDefaultDashedStyle($which_style)
    {
        // Explode "numcol-numtrans-numcol-numtrans..." into segment counts:
        $asked = explode('-', $which_style);

        if (count($asked) < 2) {
            return $this->PrintError("SetDefaultDashedStyle(): Wrong parameter '$which_style'.");
        }

        // Build the string to be evaluated later by SetDashedStyle() with $which_ndxcolor set.
        $result = '';
        $vals = array('$which_ndxcol,', 'IMG_COLOR_TRANSPARENT,');
        $index = 0;
        foreach ($asked as $n) {
            $result .= str_repeat($vals[$index], $n);
            $index = 1 - $index;
        }
        $this->default_dashed_style = "array($result)";

        return TRUE;
    }

    /*
     * Sets the style before drawing a dashed line. Defaults to $this->default_dashed_style
     *    $which_ndxcol : Color index to be used.
     */
    protected function SetDashedStyle($which_ndxcol)
    {
        // See SetDefaultDashedStyle() to understand this.
        eval ("\$style = $this->default_dashed_style;");
        return imagesetstyle($this->img, $style);
    }

    /*
     * Set line widths for each data set.
     *   $which_lw : Array of line widths in pixels, or a single value to use for all data sets.
     */
    function SetLineWidths($which_lw=NULL)
    {
        if (is_array($which_lw)) {
            $this->line_widths = $which_lw; // Use provided array
        } elseif (!is_null($which_lw)) {
            $this->line_widths = array($which_lw); // Convert value to array
        }
        return TRUE;
    }

    /*
     * Set line style ('solid' or 'dashed') for each data set.
     *   $which_ls : Array of keywords, or a single keyword to use for all data sets.
     */
    function SetLineStyles($which_ls=NULL)
    {
        if (is_array($which_ls)) {
            $this->line_styles = $which_ls; // Use provided array
        } elseif (!is_null($which_ls)) {
            $this->line_styles = ($which_ls) ? array($which_ls) : array('solid');
        }
        return TRUE;
    }

/////////////////////////////////////////////
//////////////                 TEXT and FONTS
/////////////////////////////////////////////

    /*
     * Controls the line spacing of multi-line labels.
     *   $which_spc : Line spacing factor for text
     * For GD text, this is the number of pixels between lines.
     * For TTF text, it controls line spacing in proportion to the normal
     * spacing defined by the font.
     */
    function SetLineSpacing($which_spc)
    {
        $this->line_spacing = $which_spc;
        return TRUE;
    }

    /*
     * Select the default font type to use.
     *   $which_ttf : True to default to TrueType, False to default to GD (fixed) fonts.
     * This also resets all font settings to the defaults.
     */
    function SetUseTTF($which_ttf)
    {
        $this->use_ttf = $which_ttf;
        return $this->SetDefaultFonts();
    }

    /*
     * Sets the directory name to look into for TrueType fonts.
     */
    function SetTTFPath($which_path)
    {
        if (!is_dir($which_path) || !is_readable($which_path)) {
            return $this->PrintError("SetTTFPath(): $which_path is not a valid path.");
        }
        $this->ttf_path = $which_path;
        return TRUE;
    }

    /*
     * Sets the default TrueType font and updates all fonts to that.
     * The default font might be a full path, or relative to the TTFPath,
     * so let SetFont check that it exists.
     * Side effects: Enables use of TrueType fonts as the default font type,
     * and resets all font settings.
     */
    function SetDefaultTTFont($which_font)
    {
        $this->default_ttfont = $which_font;
        return $this->SetUseTTF(TRUE);
    }

    /*
     * Return the default TrueType font name. If no default has been set,
     * this tries some likely candidates for a font which can be loaded.
     * If it finds one that works, that becomes the default TT font.
     * If there is no default and it cannot find a working font, it falls
     * back to the original PHPlot default (which will not likely work either).
     */
    protected function GetDefaultTTFont()
    {
        if (!isset($this->default_ttfont)) {
            // No default font yet. Try some common sans-serif fonts.
            $fonts = array('LiberationSans-Regular.ttf',  // For Linux with a correct GD font search path
                           'Verdana.ttf', 'Arial.ttf', 'Helvetica.ttf', // For Windows, maybe others
                           'ttf-liberation/LiberationSans-Regular.ttf', // For Debian, Ubuntu, and friends
                           'benjamingothic.ttf',  // Original PHPlot default 
                          );
            foreach ($fonts as $font) {
                // First try the font name alone, to see if GD can find and load it.
                if (@imagettfbbox(10, 0, $font, "1") !== False)
                    break;
                // If the font wasn't found, try it with the default TTF path in front.
                $font_with_path = $this->ttf_path . DIRECTORY_SEPARATOR . $font;
                if (@imagettfbbox(10, 0, $font_with_path, "1") !== False) {
                    $font = $font_with_path;
                    break;
                }
            }
            // We either have a working font, or are using the last one regardless.
            $this->default_ttfont = $font;
        }
        return $this->default_ttfont;
    }

    /*
     * Sets fonts to their defaults
     */
    protected function SetDefaultFonts()
    {
        // TTF:
        if ($this->use_ttf) {
            return $this->SetFont('generic', '', 8)
                && $this->SetFont('title', '', 14)
                && $this->SetFont('legend', '', 8)
                && $this->SetFont('x_label', '', 6)
                && $this->SetFont('y_label', '', 6)
                && $this->SetFont('x_title', '', 10)
                && $this->SetFont('y_title', '', 10);
        }
        // Fixed GD Fonts:
        return $this->SetFont('generic', 2)
            && $this->SetFont('title', 5)
            && $this->SetFont('legend', 2)
            && $this->SetFont('x_label', 1)
            && $this->SetFont('y_label', 1)
            && $this->SetFont('x_title', 3)
            && $this->SetFont('y_title', 3);
    }

    /*
     * Select a fixed (GD) font for an element.
     * This allows using a fixed font, even with SetUseTTF(True).
     *    $which_elem : The element whose font is to be changed.
     *       One of: title legend generic x_label y_label x_title y_title
     *    $which_font : A GD font number 1-5
     *    $which_spacing (optional) : Line spacing factor
     */
    function SetFontGD($which_elem, $which_font, $which_spacing = NULL)
    {
        if ($which_font < 1 || 5 < $which_font) {
            return $this->PrintError(__FUNCTION__ . ': Font size must be 1, 2, 3, 4 or 5');
        }
        if (!$this->CheckOption($which_elem,
                                'generic, title, legend, x_label, y_label, x_title, y_title',
                                __FUNCTION__)) {
            return FALSE;
        }

        // Store the font parameters: name/size, char cell height and width.
        $this->fonts[$which_elem] = array('ttf' => FALSE,
                                          'font' => $which_font,
                                          'height' => ImageFontHeight($which_font),
                                          'width' => ImageFontWidth($which_font),
                                          'line_spacing' => $which_spacing);
        return TRUE;
    }

    /*
     * Select a TrueType font for an element.
     * This allows using a TrueType font, even with SetUseTTF(False).
     *    $which_elem : The element whose font is to be changed.
     *       One of: title legend generic x_label y_label x_title y_title
     *    $which_font : A TrueType font filename or pathname.
     *    $which_size : Font point size.
     *    $which_spacing (optional) : Line spacing factor
     */
    function SetFontTTF($which_elem, $which_font, $which_size = 12, $which_spacing = NULL)
    {
        if (!$this->CheckOption($which_elem,
                                'generic, title, legend, x_label, y_label, x_title, y_title',
                                __FUNCTION__)) {
            return FALSE;
        }

        // Empty font name means use the default font.
        if (empty($which_font))
            $which_font = $this->GetDefaultTTFont();
        $path = $which_font;

        // First try the font name directly, if not then try with path.
        // Use GD imagettfbbox() to determine if this is a valid font.
        // The return $bbox is used below, if valid.
        if (($bbox = @imagettfbbox($which_size, 0, $path, "E")) === False) {
            $path = $this->ttf_path . DIRECTORY_SEPARATOR . $which_font;
            if (($bbox = @imagettfbbox($which_size, 0, $path, "E")) === False) {
                return $this->PrintError(__FUNCTION__ . ": Can't find TrueType font $which_font");
            }
        }

        // Calculate the font height and inherent line spacing. TrueType fonts have this information
        // internally, but PHP/GD has no way to directly access it. So get the bounding box size of
        // an upper-case character without descenders, and the baseline-to-baseline height.
        // Note: In practice, $which_size = $height, maybe +/-1 . But which_size is in points,
        // and height is in pixels, and someday GD may be able to tell the difference.
        // The character width is saved too, but not used by the normal text drawing routines - it
        // isn't necessarily a fixed-space font. It is used in DrawLegend.
        $height = $bbox[1] - $bbox[5];
        $width = $bbox[2] - $bbox[0];
        $bbox = ImageTTFBBox($which_size, 0, $path, "E\nE");
        $spacing = $bbox[1] - $bbox[5] - 2 * $height;

        // Store the font parameters:
        $this->fonts[$which_elem] = array('ttf' => TRUE,
                                          'font' => $path,
                                          'size' => $which_size,
                                          'height' => $height,
                                          'width' => $width,
                                          'spacing' => $spacing,
                                          'line_spacing' => $which_spacing);
        return TRUE;
    }

    /*
     * Select Fixed/TrueType font for an element. Which type of font is
     * selected depends on the $use_ttf class variable (see SetUseTTF()).
     * Before PHPlot supported mixing font types, only this function and
     * SetUseTTF were available to select an overall font type, but now
     * SetFontGD() and SetFontTTF() can be used for mixing font types.
     *    $which_elem : The element whose font is to be changed.
     *       One of: title legend generic x_label y_label x_title y_title
     *    $which_font : A number 1-5 for fixed fonts, or a TrueType font.
     *    $which_size : Ignored for Fixed fonts, point size for TrueType.
     *    $which_spacing (optional) : Line spacing factor
     */
    function SetFont($which_elem, $which_font, $which_size = 12, $line_spacing = NULL)
    {
        if ($this->use_ttf)
            return $this->SetFontTTF($which_elem, $which_font, $which_size, $line_spacing);
        return $this->SetFontGD($which_elem, $which_font, $line_spacing);
    }

    /*
     * Return the inter-line spacing for a font.
     * This is an internal function, used by ProcessText* and DrawLegend.
     *   $font : A font array variable.
     * Returns: Spacing, in pixels, between text lines.
     */
    protected function GetLineSpacing($font)
    {
        // Use the per-font line spacing preference, if set, else the global value:
        if (isset($font['line_spacing']))
            $line_spacing = $font['line_spacing'];
        else
            $line_spacing = $this->line_spacing;

        // For GD fonts, that is the spacing in pixels.
        // For TTF, adjust based on the 'natural' font spacing (see SetFontTTF):
        if ($font['ttf']) {
            $line_spacing = (int)($line_spacing * $font['spacing'] / 6.0);
        }
        return $line_spacing;
    }

    /*
     * Text drawing and sizing functions:
     * ProcessText is meant for use only by DrawText and SizeText.
     *    ProcessText(True, ...)  - Draw a block of text
     *    ProcessText(False, ...) - Just return ($width, $height) of
     *       the orthogonal bounding box containing the text.
     * ProcessText is further split into separate functions for GD and TTF
     * text, due to the size of the code.
     *
     * Horizontal and vertical alignment are relative to the drawing. That is:
     * vertical text (90 deg) gets centered along Y position with
     * v_align = 'center', and adjusted to the right of X position with
     * h_align = 'right'.  Another way to look at this is to say
     * that text rotation happens first, then alignment.
     *
     * Original multiple lines code submitted by Remi Ricard.
     * Original vertical code submitted by Marlin Viss.
     *
     * Text routines rewritten by ljb to fix alignment and position problems.
     * Here is my explanation and notes. More information and pictures will be
     * placed in the PHPlot Reference Manual.
     *
     *    + Process TTF text one line at a time, not as a block. (See below)
     *    + Flipped top vs bottom vertical alignment. The usual interpretation
     *  is: bottom align means bottom of the text is at the specified Y
     *  coordinate. For some reason, PHPlot did left/right the correct way,
     *  but had top/bottom reversed. I fixed it, and left the default valign
     *  argument as bottom, but the meaning of the default value changed.
     *
     *    For GD font text, only single-line text is handled by GD, and the
     *  basepoint is the upper left corner of each text line.
     *    For TTF text, multi-line text could be handled by GD, with the text
     *  basepoint at the lower left corner of the first line of text.
     *  (Behavior of TTF drawing routines on multi-line text is not documented.)
     *  But you cannot do left/center/right alignment on each line that way,
     *  or proper line spacing.
     *    Therefore, for either text type, we have to break up the text into
     *  lines and position each line independently.
     *
     *    There are 9 alignment modes: Horizontal = left, center, or right, and
     *  Vertical = top, center, or bottom. Alignment is interpreted relative to
     *  the image, not as the text is read. This makes sense when you consider
     *  for example X axis labels. They need to be centered below the marks
     *  (center, top alignment) regardless of the text angle.
     *  'Bottom' alignment really means baseline alignment.
     *
     *    GD font text is supported (by libgd) at 0 degrees and 90 degrees only.
     *  Multi-line or single line text works with any of the 9 alignment modes.
     *
     *    TTF text can be at any angle. The 9 alignment modes work for all angles,
     *  but the results might not be what you expect for multi-line text. See
     *  the PHPlot Reference Manual for pictures and details. In short, alignment
     *  applies to the orthogonal (aligned with X and Y axes) bounding box that
     *  contains the text, and to each line in the multi-line text box. Since
     *  alignment is relative to the image, 45 degree multi-line text aligns
     *  differently from 46 degree text.
     *
     *    Note that PHPlot allows multi-line text for the 3 titles, and they
     *  are only drawn at 0 degrees (main and X titles) or 90 degrees (Y title).
     *  Data labels can also be multi-line, and they can be drawn at any angle.
     *  -ljb 2007-11-03
     *
     */

    /*
     * ProcessTextGD() - Draw or size GD fixed-font text.
     * This is intended for use only by ProcessText().
     *    $draw_it : True to draw the text, False to just return the orthogonal width and height.
     *    $font : PHPlot font array (with 'ttf' = False) - see SetFontGD()
     *    $angle : Text angle in degrees. GD only supports 0 and 90. We treat >= 45 as 90, else 0.
     *    $x, $y : Reference point for the text (ignored if !$draw_it)
     *    $color : GD color index to use for drawing the text (ignored if !$draw_it)
     *    $text : The text to draw or size. Put a newline between lines.
     *    $h_factor : Horizontal alignment factor: 0(left), .5(center), or 1(right) (ignored if !$draw_it)
     *    $v_factor : Vertical alignment factor: 0(top), .5(center), or 1(bottom) (ignored if !$draw_it)
     * Returns: True, if drawing text, or an array of ($width, $height) if not.
     */
    protected function ProcessTextGD($draw_it, $font, $angle, $x, $y, $color, $text, $h_factor, $v_factor)
    {
        // Extract font parameters:
        $font_number = $font['font'];
        $font_width = $font['width'];
        $font_height = $font['height'];
        $line_spacing = $this->GetLineSpacing($font);

        // Break up the text into lines, trim whitespace, find longest line.
        // Save the lines and length for drawing below.
        $longest = 0;
        foreach (explode("\n", $text) as $each_line) {
            $lines[] = $line = trim($each_line);
            $line_lens[] = $line_len = strlen($line);
            if ($line_len > $longest) $longest = $line_len;
        }
        $n_lines = count($lines);

        // Width, height are based on font size and longest line, line count respectively.
        // These are relative to the text angle.
        $total_width = $longest * $font_width;
        $total_height = $n_lines * $font_height + ($n_lines - 1) * $line_spacing;

        if (!$draw_it) {
            if ($angle < 45) return array($total_width, $total_height);
            return array($total_height, $total_width);
        }

        $interline_step = $font_height + $line_spacing; // Line-to-line step

        if ($angle >= 45) {
            // Vertical text (90 degrees):
            // (Remember the alignment convention with vertical text)
            // For 90 degree text, alignment factors change like this:
            $temp = $v_factor;
            $v_factor = $h_factor;
            $h_factor = 1 - $temp;

            $draw_func = 'ImageStringUp';

            // Rotation matrix "R" for 90 degrees (with Y pointing down):
            $r00 = 0;  $r01 = 1;
            $r10 = -1; $r11 = 0;

        } else {
            // Horizontal text (0 degrees):
            $draw_func = 'ImageString';

            // Rotation matrix "R" for 0 degrees:
            $r00 = 1; $r01 = 0;
            $r10 = 0; $r11 = 1;
        }

        // Adjust for vertical alignment (horizontal text) or horizontal alignment (vertical text):
        $factor = (int)($total_height * $v_factor);
        $xpos = $x - $r01 * $factor;
        $ypos = $y - $r11 * $factor;

        // Debug callback provides the bounding box:
        if ($this->GetCallback('debug_textbox')) {
            if ($angle >= 45) {
                $bbox_width  = $total_height;
                $bbox_height = $total_width;
                $px = $xpos;
                $py = $ypos - (1 - $h_factor) * $total_width;
            } else {
                $bbox_width  = $total_width;
                $bbox_height = $total_height;
                $px = $xpos - $h_factor * $total_width;
                $py = $ypos;
            }
            $this->DoCallback('debug_textbox', $px, $py, $bbox_width, $bbox_height);
        }

        for ($i = 0; $i < $n_lines; $i++) {

            // Adjust for alignment of this line within the text block:
            $factor = (int)($line_lens[$i] * $font_width * $h_factor);
            $x = $xpos - $r00 * $factor;
            $y = $ypos - $r10 * $factor;

            // Call ImageString or ImageStringUp:
            $draw_func($this->img, $font_number, $x, $y, $lines[$i], $color);

            // Step to the next line of text. This is a rotation of (x=0, y=interline_spacing)
            $xpos += $r01 * $interline_step;
            $ypos += $r11 * $interline_step;
        }
        return TRUE;
    }

    /*
     * ProcessTextTTF() - Draw or size TTF text.
     * This is intended for use only by ProcessText().
     *    $draw_it : True to draw the text, False to just return the orthogonal width and height.
     *    $font : PHPlot font array (with 'ttf' = True) - see SetFontTTF()
     *    $angle : Text angle in degrees.
     *    $x, $y : Reference point for the text (ignored if !$draw_it)
     *    $color : GD color index to use for drawing the text (ignored if !$draw_it)
     *    $text : The text to draw or size. Put a newline between lines.
     *    $h_factor : Horizontal alignment factor: 0(left), .5(center), or 1(right) (ignored if !$draw_it)
     *    $v_factor : Vertical alignment factor: 0(top), .5(center), or 1(bottom) (ignored if !$draw_it)
     * Returns: True, if drawing text, or an array of ($width, $height) if not.
     */
    protected function ProcessTextTTF($draw_it, $font, $angle, $x, $y, $color, $text, $h_factor, $v_factor)
    {
        // Extract font parameters (see SetFontTTF):
        $font_file = $font['font'];
        $font_size = $font['size'];
        $font_height = $font['height'];
        $line_spacing = $this->GetLineSpacing($font);

        // Break up the text into lines, trim whitespace.
        // Calculate the total width and height of the text box at 0 degrees.
        // Save the trimmed lines and their widths for later when drawing.
        // To get uniform spacing, don't use the actual line heights.
        // Total height = Font-specific line heights plus inter-line spacing.
        // Total width = width of widest line.
        // Last Line Descent is the offset from the bottom to the text baseline.
        // Note: For some reason, ImageTTFBBox uses (-1,-1) as the reference point.
        //   So 1+bbox[1] is the baseline to bottom distance.
        $total_width = 0;
        $lastline_descent = 0;
        foreach (explode("\n", $text) as $each_line) {
            $lines[] = $line = trim($each_line);
            $bbox = ImageTTFBBox($font_size, 0, $font_file, $line);
            $line_widths[] = $width = $bbox[2] - $bbox[0];
            if ($width > $total_width) $total_width = $width;
            $lastline_descent = 1 + $bbox[1];
        }
        $n_lines = count($lines);
        $total_height = $n_lines * $font_height + ($n_lines - 1) * $line_spacing;

        // Calculate the rotation matrix for the text's angle. Remember that GD points Y down,
        // so the sin() terms change sign.
        $theta = deg2rad($angle);
        $cos_t = cos($theta);
        $sin_t = sin($theta);
        $r00 = $cos_t;    $r01 = $sin_t;
        $r10 = -$sin_t;   $r11 = $cos_t;

        // Make a bounding box of the right size, with upper left corner at (0,0).
        // By convention, the point order is: LL, LR, UR, UL.
        // Note this is still working with the text at 0 degrees.
        // When sizing text (SizeText), use the overall size with descenders.
        //   This tells the caller how much room to leave for the text.
        // When drawing text (DrawText), use the size without descenders - that
        //   is, down to the baseline. This is for accurate positioning.
        $b[0] = 0;
        if ($draw_it) {
            $b[1] = $total_height;
        } else {
            $b[1] = $total_height + $lastline_descent;
        }
        $b[2] = $total_width;  $b[3] = $b[1];
        $b[4] = $total_width;  $b[5] = 0;
        $b[6] = 0;             $b[7] = 0;

        // Rotate the bounding box, then offset to the reference point:
        for ($i = 0; $i < 8; $i += 2) {
            $x_b = $b[$i];
            $y_b = $b[$i+1];
            $c[$i]   = $x + $r00 * $x_b + $r01 * $y_b;
            $c[$i+1] = $y + $r10 * $x_b + $r11 * $y_b;
        }

        // Get an orthogonal (aligned with X and Y axes) bounding box around it, by
        // finding the min and max X and Y:
        $bbox_ref_x = $bbox_max_x = $c[0];
        $bbox_ref_y = $bbox_max_y = $c[1];
        for ($i = 2; $i < 8; $i += 2) {
            $x_b = $c[$i];
            if ($x_b < $bbox_ref_x) $bbox_ref_x = $x_b;
            elseif ($bbox_max_x < $x_b) $bbox_max_x = $x_b;
            $y_b = $c[$i+1];
            if ($y_b < $bbox_ref_y) $bbox_ref_y = $y_b;
            elseif ($bbox_max_y < $y_b) $bbox_max_y = $y_b;
        }
        $bbox_width = $bbox_max_x - $bbox_ref_x;
        $bbox_height = $bbox_max_y - $bbox_ref_y;

        if (!$draw_it) {
            // Return the bounding box, rounded up (so it always contains the text):
            return array((int)ceil($bbox_width), (int)ceil($bbox_height));
        }

        $interline_step = $font_height + $line_spacing; // Line-to-line step

        // Calculate the offsets from the supplied reference point to the
        // upper-left corner of the text.
        // Start at the reference point at the upper left corner of the bounding
        // box (bbox_ref_x, bbox_ref_y) then adjust it for the 9 point alignment.
        // h,v_factor are 0,0 for top,left, .5,.5 for center,center, 1,1 for bottom,right.
        //    $off_x = $bbox_ref_x + $bbox_width * $h_factor - $x;
        //    $off_y = $bbox_ref_y + $bbox_height * $v_factor - $y;
        // Then use that offset to calculate back to the supplied reference point x, y
        // to get the text base point.
        //    $qx = $x - $off_x;
        //    $qy = $y - $off_y;
        // Reduces to:
        $qx = 2 * $x - $bbox_ref_x - $bbox_width * $h_factor;
        $qy = 2 * $y - $bbox_ref_y - $bbox_height * $v_factor;

        // Check for debug callback. Don't calculate bounding box unless it is wanted.
        if ($this->GetCallback('debug_textbox')) {
            // Calculate the orthogonal bounding box coordinates for debug testing.

            // qx, qy is upper left corner relative to the text.
            // Calculate px,py: upper left corner (absolute) of the bounding box.
            // There are 4 equation sets for this, depending on the quadrant:
            if ($sin_t > 0) {
                if ($cos_t > 0) {
                    // Quadrant: 0d - 90d:
                    $px = $qx; $py = $qy - $total_width * $sin_t;
                } else {
                    // Quadrant: 90d - 180d:
                   $px = $qx + $total_width * $cos_t; $py = $qy - $bbox_height;
                }
            } else {
                if ($cos_t < 0) {
                    // Quadrant: 180d - 270d:
                    $px = $qx - $bbox_width; $py = $qy + $total_height * $cos_t;
                } else {
                    // Quadrant: 270d - 360d:
                    $px = $qx + $total_height * $sin_t; $py = $qy;
                }
            }
            $this->DoCallback('debug_textbox', $px, $py, $bbox_width, $bbox_height);
        }

        // Since alignment is applied after rotation, which parameter is used
        // to control alignment of each line within the text box varies with
        // the angle.
        //   Angle (degrees):       Line alignment controlled by:
        //  -45 < angle <= 45          h_align
        //   45 < angle <= 135         reversed v_align
        //  135 < angle <= 225         reversed h_align
        //  225 < angle <= 315         v_align
        if ($cos_t >= $sin_t) {
            if ($cos_t >= -$sin_t) $line_align_factor = $h_factor;
            else $line_align_factor = $v_factor;
        } else {
            if ($cos_t >= -$sin_t) $line_align_factor = 1-$v_factor;
            else $line_align_factor = 1-$h_factor;
        }

        // Now we have the start point, spacing and in-line alignment factor.
        // We are finally ready to start drawing the text, line by line.
        for ($i = 0; $i < $n_lines; $i++) {

            // For drawing TTF text, the reference point is the left edge of the
            // text baseline (not the lower left corner of the bounding box).
            // The following also adjusts for horizontal (relative to
            // the text) alignment of the current line within the box.
            // What is happening is rotation of this vector by the text angle:
            //    (x = (total_width - line_width) * factor, y = font_height)

            $width_factor = ($total_width - $line_widths[$i]) * $line_align_factor;
            $rx = $qx + $r00 * $width_factor + $r01 * $font_height;
            $ry = $qy + $r10 * $width_factor + $r11 * $font_height;

            // Finally, draw the text:
            ImageTTFText($this->img, $font_size, $angle, $rx, $ry, $color, $font_file, $lines[$i]);

            // Step to position of next line.
            // This is a rotation of (x=0,y=height+line_spacing) by $angle:
            $qx += $r01 * $interline_step;
            $qy += $r11 * $interline_step;
        }
        return TRUE;
    }

    /*
     * ProcessText() - Wrapper for ProcessTextTTF() and ProcessTextGD(). See notes above.
     * This is intended for use from within PHPlot only, and only by DrawText() and SizeText().
     *    $draw_it : True to draw the text, False to just return the orthogonal width and height.
     *    $font : PHPlot font array, or NULL or empty string to use 'generic'
     *    $angle : Text angle in degrees
     *    $x, $y : Reference point for the text (ignored if !$draw_it)
     *    $color : GD color index to use for drawing the text (ignored if !$draw_it)
     *    $text : The text to draw or size. Put a newline between lines.
     *    $halign : Horizontal alignment: left, center, or right (ignored if !$draw_it)
     *    $valign : Vertical alignment: top, center, or bottom (ignored if !$draw_it)
     *      Note: Alignment is relative to the image, not the text.
     * Returns: True, if drawing text, or an array of ($width, $height) if not.
     */
    protected function ProcessText($draw_it, $font, $angle, $x, $y, $color, $text, $halign, $valign)
    {
        // Empty text case:
        if ($text === '') {
            if ($draw_it) return TRUE;
            return array(0, 0);
        }

        // Calculate width and height offset factors using the alignment args:
        if ($valign == 'top') $v_factor = 0;
        elseif ($valign == 'center') $v_factor = 0.5;
        else $v_factor = 1.0; // 'bottom'
        if ($halign == 'left') $h_factor = 0;
        elseif ($halign == 'center') $h_factor = 0.5;
        else $h_factor = 1.0; // 'right'

        // Apply a default font. This is mostly for external (callback) users.
        if (empty($font)) $font = $this->fonts['generic'];

        if ($font['ttf']) {
            return $this->ProcessTextTTF($draw_it, $font, $angle, $x, $y, $color, $text,
                                         $h_factor, $v_factor);
        }
        return $this->ProcessTextGD($draw_it, $font, $angle, $x, $y, $color, $text, $h_factor, $v_factor);
    }

    /*
     * Draws a block of text. See comments above before ProcessText().
     *    $which_font : PHPlot font array, or NULL or empty string to use 'generic'
     *    $which_angle : Text angle in degrees
     *    $which_xpos, $which_ypos: Reference point for the text
     *    $which_color : GD color index to use for drawing the text
     *    $which_text :  The text to draw, with newlines (\n) between lines.
     *    $which_halign : Horizontal (relative to the image) alignment: left, center, or right.
     *    $which_valign : Vertical (relative to the image) alignment: top, center, or bottom.
     * Note: This function should be considered 'protected', and is not documented for public use.
     */
    function DrawText($which_font, $which_angle, $which_xpos, $which_ypos, $which_color, $which_text,
                      $which_halign = 'left', $which_valign = 'bottom')
    {
        return $this->ProcessText(TRUE,
                           $which_font, $which_angle, $which_xpos, $which_ypos,
                           $which_color, $which_text, $which_halign, $which_valign);
    }

    /*
     * Returns the size of block of text. This is the orthogonal width and height of a bounding
     * box aligned with the X and Y axes of the text. Only for angle=0 is this the actual
     * width and height of the text block, but for any angle it is the amount of space needed
     * to contain the text.
     *    $which_font : PHPlot font array, or NULL or empty string to use 'generic'
     *    $which_angle : Text angle in degrees
     *    $which_text :  The text to draw, with newlines (\n) between lines.
     * Returns a two element array with: $width, $height.
     * This is just a wrapper for ProcessText() - see above.
     * Note: This function should be considered 'protected', and is not documented for public use.
     */
    function SizeText($which_font, $which_angle, $which_text)
    {
        // Color, position, and alignment are not used when calculating the size.
        return $this->ProcessText(FALSE,
                           $which_font, $which_angle, 0, 0, 1, $which_text, '', '');
    }

/////////////////////////////////////////////
///////////            INPUT / OUTPUT CONTROL
/////////////////////////////////////////////

    /*
     * Sets output file format to $format (jpg, png, ...)
     */
    function SetFileFormat($format)
    {
        $asked = $this->CheckOption($format, 'jpg, png, gif, wbmp', __FUNCTION__);
        if (!$asked) return FALSE;
        switch ($asked) {
        case 'jpg':
            $format_test = IMG_JPG;
            break;
        case 'png':
            $format_test = IMG_PNG;
            break;
        case 'gif':
            $format_test = IMG_GIF;
            break;
        case 'wbmp':
            $format_test = IMG_WBMP;
            break;
        }
        if (!(imagetypes() & $format_test)) {
            return $this->PrintError("SetFileFormat(): File format '$format' not supported");
        }
        $this->file_format = $asked;
        return TRUE;
    }

    /*
     * Selects an input file to be used as graph background and scales or tiles this image
     * to fit the sizes.
     *   $input_file : Path to the file to be used (jpeg, png and gif accepted)
     *   $mode : 'centeredtile', 'tile', or 'scale' (the image to the graph's size)
     */
    function SetBgImage($input_file, $mode='centeredtile')
    {
        $this->bgmode = $this->CheckOption($mode, 'tile, centeredtile, scale', __FUNCTION__);
        $this->bgimg  = $input_file;
        return (boolean)$this->bgmode;
    }

    /*
     * Selects an input file to be used as plot area background and scales or tiles this image
     * to fit the sizes.
     *   $input_file : Path to the file to be used (jpeg, png and gif accepted)
     *   $mode : 'centeredtile', 'tile', or 'scale' (the image to the graph's size)
     */
    function SetPlotAreaBgImage($input_file, $mode='tile')
    {
        $this->plotbgmode = $this->CheckOption($mode, 'tile, centeredtile, scale', __FUNCTION__);
        $this->plotbgimg  = $input_file;
        return (boolean)$this->plotbgmode;
    }

    /*
     * Sets the name of the file to be used as output file.
     */
    function SetOutputFile($which_output_file)
    {
        $this->output_file = $which_output_file;
        return TRUE;
    }

    /*
     * Sets the output image as 'inline', that is: no Content-Type headers are sent
     * to the browser. Needed if you want to embed the images.
     */
    function SetIsInline($which_ii)
    {
        $this->is_inline = (bool)$which_ii;
        return TRUE;
    }

    /*
     * Get the MIME type and GD output function name for the current file type.
     */
    protected function GetImageType(&$mime_type, &$output_f)
    {
        switch ($this->file_format) {
        case 'png':
            $mime_type = 'image/png';
            $output_f = 'imagepng';
            break;
        case 'jpg':
            $mime_type = 'image/jpeg';
            $output_f = 'imagejpeg';
            break;
        case 'gif':
            $mime_type = 'image/gif';
            $output_f = 'imagegif';
            break;
        case 'wbmp':
            $mime_type = 'image/wbmp';
            $output_f = 'imagewbmp';
            break;
        default:
            // Report the error on PrintImage, because that is where this code used to be.
            return $this->PrintError('PrintImage(): Please select an image type!');
        }
        return TRUE;
    }

    /*
     * Output the generated image to standard output or to a file.
     */
    function PrintImage()
    {
        // Browser cache stuff submitted by Thiemo Nagel
        if ( (! $this->browser_cache) && (! $this->is_inline)) {
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
        }

        // Get MIME type and GD output function name:
        if (!$this->GetImageType($mime_type, $output_f)) return FALSE;

        if (!$this->is_inline) {
            Header("Content-type: $mime_type");
        }
        if ($this->is_inline && $this->output_file != '') {
            $output_f($this->img, $this->output_file);
        } else {
            $output_f($this->img);
        }
        return TRUE;
    }

    /*
     * Return the image data, as raw data, base64 encoded, or data URL (see RFC2397).
     */
    function EncodeImage($encoding = 'dataurl')
    {
        $enc = $this->CheckOption($encoding, 'dataurl, raw, base64', __FUNCTION__);
        if (!$enc || !$this->GetImageType($mime_type, $output_f)) return FALSE;
        ob_start();
        $output_f($this->img);
        switch ($enc) {
        case 'raw':
            return ob_get_clean();
        case 'base64':
            return base64_encode(ob_get_clean());
        default:  // 'dataurl', checked above.
            return "data:$mime_type;base64,\n" . chunk_split(base64_encode(ob_get_clean()));
        }
    }

    /*
     *  Error handling for 'fatal' errors:
     *   $error_message       Text of the error message
     *  Output from PHPlot is expected to be an image file, such as when
     *  handling an <img> tag browser request. So it is not permitted to
     *  output text to standard output. (You should have display_errors=off)
     *  Here is how PHPlot handles fatal errors:
     *    + Draw the error message into an image, and output the image (to
     *      standard output, or a file, as directed).
     *      The error image is suppressed if suppress_error_image is True.
     *    + If there is no GD image in the PHPlot object (early failure),
     *      output no image, and produce an HTTP error header instead.
     *    + Trigger a user-level error containing the error message.
     *      If no error handler was set up, the PHP will log the
     *      error and exit with non-zero status.
     *
     *  PrintError() and DrawError() are now equivalent. Both are provided for
     *  compatibility. (In earlier releases, PrintError sent the message to
     *  stdout only, and DrawError sent it in an image only.)
     *
     *  This function does not return, unless the calling script has set up
     *  an error handler which does not exit. In that case, PrintError will
     *  return False. But not all of PHPlot may handle this correctly, so
     *  it is probably a bad idea for an error handler to return.
     */
    protected function PrintError($error_message)
    {
        // Be sure not to loop recursively, e.g. PrintError - PrintImage - PrintError.
        if (isset($this->in_error)) return FALSE;
        $this->in_error = TRUE;

        // Output an image containing the error message:
        if (empty($this->suppress_error_image)) {
            if (!empty($this->img)) {
                $ypos = $this->image_height/2;
                $xpos = $this->image_width/2;
                $bgcolor = ImageColorResolve($this->img, 255, 255, 255);
                $fgcolor = ImageColorResolve($this->img, 0, 0, 0);
                ImageFilledRectangle($this->img, 0, 0, $this->image_width, $this->image_height, $bgcolor);

                // Switch to built-in fonts, in case of error with TrueType fonts:
                $this->SetUseTTF(FALSE);

                $this->DrawText($this->fonts['generic'], 0, $xpos, $ypos, $fgcolor,
                                wordwrap($error_message), 'center', 'center');

                $this->PrintImage();
            } elseif (!$this->is_inline) {
                Header('HTTP/1.0 500 Internal Server Error');
            }
        }
        trigger_error($error_message, E_USER_ERROR);
        unset($this->in_error);
        return FALSE;  // In case error handler returns, rather than doing exit().
    }

    /*
     * Display an error message and exit.
     * This is provided for backward compatibility only. Use PrintError() instead.
     *   $error_message       Text of the error message
     *   $where_x, $where_y   Ignored, provided for compatibility.
     */
    protected function DrawError($error_message, $where_x = NULL, $where_y = NULL)
    {
        return $this->PrintError($error_message);
    }

    /*
     * Set error behavior. On failure, PHPlot normally creates an error image.
     */
    function SetFailureImage($error_image)
    {
        $this->suppress_error_image = !$error_image;
    }

/////////////////////////////////////////////
///////////                            LABELS
/////////////////////////////////////////////

    /*
     * Sets position for X data labels.
     * For vertical plots, these are X axis data labels, showing label strings from the data array.
     *    Accepted positions are: plotdown, plotup, both, none.
     * For horizontal plots (bar, stackedbar only), these are X data value labels, show the data values.
     *    Accepted positions are: plotin, plotstack, none.
     */
    function SetXDataLabelPos($which_xdlp)
    {
        $which_xdlp = $this->CheckOption($which_xdlp, 'plotdown, plotup, both, none, plotin, plotstack',
                                         __FUNCTION__);
        if (!$which_xdlp) return FALSE;
        $this->x_data_label_pos = $which_xdlp;

        return TRUE;
    }

    /*
     * Sets position for Y data labels.
     * For vertical plots (where available), these are Y data value labels, showing the data values.
     *    Accepted positions are: plotin, plotstack, none.
     * For horizontal plots, these are Y axis data labels, showing label strings from the data array.
     *    Accepted positions are: plotleft, plotright, both, none.
     */
    function SetYDataLabelPos($which_ydlp)
    {
        $which_ydlp = $this->CheckOption($which_ydlp, 'plotleft, plotright, both, none, plotin, plotstack',
                                          __FUNCTION__);
        if (!$which_ydlp) return FALSE;
        $this->y_data_label_pos = $which_ydlp;

        return TRUE;
    }

    /*
     * Set position for X tick labels.
     */
    function SetXTickLabelPos($which_xtlp)
    {
        $which_xtlp = $this->CheckOption($which_xtlp, 'plotdown, plotup, both, xaxis, none',
                                         __FUNCTION__);
        if (!$which_xtlp) return FALSE;
        $this->x_tick_label_pos = $which_xtlp;

        return TRUE;
    }

    /*
     * Set position for Y tick labels.
     */
    function SetYTickLabelPos($which_ytlp)
    {
        $which_ytlp = $this->CheckOption($which_ytlp, 'plotleft, plotright, both, yaxis, none',
                                         __FUNCTION__);
        if (!$which_ytlp) return FALSE;
        $this->y_tick_label_pos = $which_ytlp;

        return TRUE;
    }

    /*
     * Set formatting type for tick and data labels on X or Y axis.
     * This implements the 4 functions Set[XY]LabelType() and Set[XY]DataLabelType().
     *    $mode  : 'x', 'y', 'xd', or 'yd' - which type of label to configure.
     *        'x' and 'y' set the type for tick labels, and the default type for data labels
     *        if they are not separately configured. 'xd' and 'yd' set the type for data labels.
     *    $args  : Variable arguments, passed as an array.
     *       [0] = $type (required) : Label type. 'data', 'time', 'printf', or 'custom'.
     *     For type 'data':
     *       [1] = $precision (optional). Numeric precision. Can also be set by SetPrecision[XY]().
     *       [2] = $prefix (optional) - prefix string for labels.
     *       [3] = $suffix (optional) - suffix string for labels. This replaces data_units_text.
     *     For type 'time':
     *       [1] = $format for strftime (optional). Can also be set by Set[XY]TimeFormat().
     *     For type 'printf':
     *       [1] = $format (optional) for sprintf.
     *     For type 'custom':
     *       [1] = $callback (required) - Custom function or array of (instance,method) to call.
     *       [2] = $argument (optional) - Pass-through argument for the formatting function.
     */
    protected function SetLabelType($mode, $args)
    {
        if (!$this->CheckOption($mode, 'x, y, xd, yd', __FUNCTION__))
            return FALSE;

        $type = isset($args[0]) ? $args[0] : '';
        $format =& $this->label_format[$mode];  // Shorthand reference to format storage variables
        switch ($type) {
        case 'data':
            if (isset($args[1]))
                $format['precision'] = $args[1];
            elseif (!isset($format['precision']))
                $format['precision'] = 1;
            $format['prefix'] = isset($args[2]) ? $args[2] : '';
            $format['suffix'] = isset($args[3]) ? $args[3] : '';
            break;

        case 'time':
            if (isset($args[1]))
                $format['time_format'] = $args[1];
            elseif (!isset($format['time_format']))
                $format['time_format'] = '%H:%M:%S';
            break;

        case 'printf':
            if (isset($args[1]))
                $format['printf_format'] = $args[1];
            elseif (!isset($format['printf_format']))
                $format['printf_format'] = '%e';
            break;

        case 'custom':
            if (isset($args[1])) {
                $format['custom_callback'] = $args[1];
                $format['custom_arg'] = isset($args[2]) ? $args[2] : NULL;
            } else {
                $type = ''; // Error, 'custom' without a function, set to no-format mode.
            }
            break;

        case '':
        case 'title':   // Retained for backwards compatibility?
            break;

        default:
            $this->CheckOption($type, 'data, time, printf, custom', __FUNCTION__);
            $type = '';
        }
        $format['type'] = $type;
        return (boolean)$type;
    }

    /*
     * Select label formating for X tick labels, and for X data labels
     * (unless SetXDataLabelType was called).
     * See SetLabelType() for details.
     */
    function SetXLabelType()  // Variable arguments: $type, ...
    {
        $args = func_get_args();
        return $this->SetLabelType('x', $args);
    }

    /*
     * Select label formatting for X data labels, overriding SetXLabelType.
     */
    function SetXDataLabelType()  // Variable arguments: $type, ...
    {
        $args = func_get_args();
        return $this->SetLabelType('xd', $args);
    }

    /*
     * Select label formating for Y tick labels, and for Y data labels
     * (unless SetYDataLabelType was called).
     * See SetLabelType() for details.
     */
    function SetYLabelType()  // Variable arguments: $type, ...
    {
        $args = func_get_args();
        return $this->SetLabelType('y', $args);
    }

    /*
     * Select label formatting for Y data labels, overriding SetYLabelType.
     */
    function SetYDataLabelType()  // Variable arguments: $type, ...
    {
        $args = func_get_args();
        return $this->SetLabelType('yd', $args);
    }

    /*
     * Set the date/time format code for X labels.
     * Note: Use of SetXLabelType('time', $which_xtf) is preferred, because
     * SetXTimeFormat does not also enable date/time formatting.
     */
    function SetXTimeFormat($which_xtf)
    {
        $this->label_format['x']['time_format'] = $which_xtf;
        return TRUE;
    }

    /*
     * Set the date/time format code for Y labels.
     * Note: Use of SetYLabelType('time', $which_ytf) is preferred, because
     * SetYTimeFormat does not also enable date/time formatting.
     */
    function SetYTimeFormat($which_ytf)
    {
        $this->label_format['y']['time_format'] = $which_ytf;
        return TRUE;
    }

    /*
     * Set number format parameters (decimal point and thousands separator) for
     * 'data' mode label formatting, overriding the locale-defaults.
     */
    function SetNumberFormat($decimal_point, $thousands_sep)
    {
        $this->decimal_point = $decimal_point;
        $this->thousands_sep = $thousands_sep;
        return TRUE;
    }

    /*
     * Set the text angle for X labels to $which_xla degrees.
     */
    function SetXLabelAngle($which_xla)
    {
        $this->x_label_angle = $which_xla;
        return TRUE;
    }

    /*
     * Set the text angle for Y labels to $which_xla degrees.
     */
    function SetYLabelAngle($which_yla)
    {
        $this->y_label_angle = $which_yla;
        return TRUE;
    }

    /*
     * Set the angle for X Data Labels to $which_xdla degrees.
     * If not used, this defaults to the value set with SetXLabelAngle.
     */
    function SetXDataLabelAngle($which_xdla)
    {
        $this->x_data_label_angle = $which_xdla;
        return TRUE;
    }

    /*
     * Set the angle for Y Data Labels to $which_ydla degrees.
     * If not used, this defaults to zero (unlike X data labels).
     */
    function SetYDataLabelAngle($which_ydla)
    {
        $this->y_data_label_angle = $which_ydla;
        return TRUE;
    }

/////////////////////////////////////////////
///////////                              MISC
/////////////////////////////////////////////

    /*
     * Checks the validity of an option.
     *   $which_opt  String to check, such as the provided value of a function argument.
     *   $which_acc  String of accepted choices. Must be lower-case, and separated
     *               by exactly ', ' (comma, space).
     *   $which_func Name of the calling function, for error messages.
     * Returns the supplied option value, downcased and trimmed, if it is valid.
     * Reports an error if the supplied option is not valid.
     */
    protected function CheckOption($which_opt, $which_acc, $which_func)
    {
        $asked = strtolower(trim($which_opt));

        // Look for the supplied value in a comma/space separated list.
        if (strpos(", $which_acc,", ", $asked,") !== FALSE)
            return $asked;

        $this->PrintError("$which_func(): '$which_opt' not in available choices: '$which_acc'.");
        return NULL;
    }

    /*
     * Checks the validity of an array of options.
     *   $opt  Array or string to check.
     *   $acc  String of accepted choices. Must be lower-case, and separated
     *               by exactly ', ' (comma, space).
     *   $func Name of the calling function, for error messages.
     * Returns a array option value(s), downcased and trimmed, if all entries in $opt are valid.
     * Reports an error if any supplied option is not valid. Returns NULL if the error handler returns.
     */
    protected function CheckOptionArray($opt, $acc, $func)
    {
        $opt_array = (array)$opt;
        $result = array();
        foreach ($opt_array as $option) {
            $choice = $this->CheckOption($option, $acc, $func);
            if (is_null($choice)) return NULL; // In case CheckOption error handler returns
            $result[] = $choice;
        }
        return $result;
    }

    /*
     * Check compatibility of a plot type and data type.
     * This is called by the plot-type-specific drawing functions.
     *   $valid_types  String of supported data types. Multiple values must be
     *      separated by exactly ', ' (comma, space).
     * Returns True if the type is valid for this plot.
     * Reports an error if the data type is not value. If the error is handled and
     *   the handler returns, this returns False.
     */
    protected function CheckDataType($valid_types)
    {
        if (strpos(", $valid_types,", ", $this->data_type,") !== FALSE)
            return TRUE;

        $this->PrintError("Data type '$this->data_type' is not valid for '$this->plot_type' plots."
               . " Supported data type(s): '$valid_types'");
        return FALSE;
    }

    /*
     * Decode the data type into variables used to determine how to process a data array.
     * The goal is minimize which functions understand the actual data type values.
     * This sets the datatype_* variables for use by other member functions.
     *   datatype_implied : Implicit independent variable (e.g. text-data vs data-data)
     *   datatype_swapped_xy : Swapped X/Y (horizontal plot)
     *   datatype_error_bars : Data array has error bar data
     *   datatype_pie_single : Data array is for a pie chart with one row per slice
     *   datatype_yz : Data array contains pairs of Y and Z for each X.
     */
    protected function DecodeDataType()
    {
        $dt = $this->data_type;

        $this->datatype_implied = ($dt == 'text-data' || $dt == 'text-data-single'
                                || $dt == 'text-data-yx');
        $this->datatype_swapped_xy = ($dt == 'text-data-yx' || $dt == 'data-data-yx');
        $this->datatype_error_bars = ($dt == 'data-data-error');
        $this->datatype_pie_single = ($dt == 'text-data-single');
        $this->datatype_yz = ($dt == 'data-data-xyz');
    }

    /*
     * Make sure the data array is populated, and calculate the number of columns.
     * This is called from DrawGraph. Calculates data_columns, which is the
     * maximum number of dependent variable values (usually Y) in the data array rows.
     * (For pie charts, this is the number of slices.)
     * This depends on the data_type, unlike records_per_group (which was
     * previously used to pad style arrays, but is not accurate).
     * Returns True if the data array is OK, else reports an error (and may return False).
     * Note error messages refer to the caller, the public DrawGraph().
     */
    protected function CheckDataArray()
    {
        // Test for missing image, which really should never happen.
        if (!$this->img) {
            return $this->PrintError('DrawGraph(): No image resource allocated');
        }

        // Test for missing or empty data array:
        if (empty($this->data) || !is_array($this->data)) {
            return $this->PrintError("DrawGraph(): No data array");
        }
        if ($this->total_records == 0) {
            return $this->PrintError('DrawGraph(): Empty data set');
        }

        // Decode the data type into functional flags.
        $this->DecodeDataType();

        // Calculate the maximum number of dependent values per independent value
        // (e.g. Y for each X), or the number of pie slices. Also validate the rows.
        $skip = $this->datatype_implied ? 1 : 2; // Skip factor for data label and independent variable
        if ($this->datatype_error_bars) {
            $this->data_columns = (int)(($this->records_per_group - $skip) / 3);
            // Validate the data array for error plots: (label, X, then groups of Y, +err, -err):
            for ($i = 0; $i < $this->num_data_rows; $i++) {
                if ($this->num_recs[$i] < $skip || ($this->num_recs[$i] - $skip) % 3 != 0)
                    return $this->PrintError("DrawGraph(): Invalid $this->data_type data array (row $i)");
            }
        } elseif ($this->datatype_pie_single) {
            $this->data_columns = $this->num_data_rows; // Special case for this type of pie chart.
            // Validate the data array for text-data-single pie charts. Requires 1 value per row.
            for ($i = 0; $i < $this->num_data_rows; $i++) {
                if ($this->num_recs[$i] != 2)
                    return $this->PrintError("DrawGraph(): Invalid $this->data_type data array (row $i)");
            }
        } elseif ($this->datatype_yz) {
            $this->data_columns = (int)(($this->records_per_group - $skip) / 2); //  (y, z) pairs
            // Validate the data array for plots using X, Y, Z: (label, X, then pairs of Y, Z)
            for ($i = 0; $i < $this->num_data_rows; $i++) {
                if ($this->num_recs[$i] < $skip || ($this->num_recs[$i] - $skip) % 2 != 0)
                    return $this->PrintError("DrawGraph(): Invalid $this->data_type data array (row $i)");
            }
        } else {
            $this->data_columns = $this->records_per_group - $skip;
            // Validate the data array for non-error plots:
            for ($i = 0; $i < $this->num_data_rows; $i++) {
                if ($this->num_recs[$i] < $skip)
                    return $this->PrintError("DrawGraph(): Invalid $this->data_type data array (row $i)");
            }
        }
        return TRUE;
    }

    /*
     * Control headers for browser-side image caching.
     *   $which_browser_cache : True to allow browsers to cache the image.
     */
    function SetBrowserCache($which_browser_cache)
    {
        $this->browser_cache = $which_browser_cache;
        return TRUE;
    }

    /*
     * Set whether DrawGraph automatically outputs the image too.
     *   $which_pi : True to have DrawGraph call PrintImage at the end.
     */
    function SetPrintImage($which_pi)
    {
        $this->print_image = $which_pi;
        return TRUE;
    }

    /*
     * Set border for the plot area.
     * Accepted values are: left, right, top, bottom, sides, none, full or an array of those.
     */
    function SetPlotBorderType($pbt)
    {
        $this->plot_border_type = $this->CheckOptionArray($pbt, 'left, right, top, bottom, sides, none, full',
                                                          __FUNCTION__);
        return !empty($this->plot_border_type);
    }

    /*
     * Set border style for the image.
     * Accepted values are: raised, plain, solid, none
     *  'solid' is the same as 'plain' except it fixes the color (see DrawImageBorder)
     */
    function SetImageBorderType($sibt)
    {
        $this->image_border_type = $this->CheckOption($sibt, 'raised, plain, solid, none', __FUNCTION__);
        return (boolean)$this->image_border_type;
    }

    /*
     * Set border width for the image to $width in pixels.
     */
    function SetImageBorderWidth($width)
    {
        $this->image_border_width = $width;
        return TRUE;
    }

    /*
     * Enable or disable drawing of the plot area background color.
     */
    function SetDrawPlotAreaBackground($dpab)
    {
        $this->draw_plot_area_background = (bool)$dpab;
        return TRUE;
    }

    /*
     * Enable or disable drawing of the X grid lines.
     */
    function SetDrawXGrid($dxg)
    {
        $this->draw_x_grid = (bool)$dxg;
        return TRUE;
    }

    /*
     * Enable or disable drawing of the Y grid lines.
     */
    function SetDrawYGrid($dyg)
    {
        $this->draw_y_grid = (bool)$dyg;
        return TRUE;
    }

    /*
     * Select dashed or solid grid lines.
     *   $ddg : True for dashed grid lines, false for solid grid lines.
     */
    function SetDrawDashedGrid($ddg)
    {
        $this->dashed_grid = (bool)$ddg;
        return TRUE;
    }

    /*
     * Enable or disable drawing of X Data Label Lines.
     */
    function SetDrawXDataLabelLines($dxdl)
    {
        $this->draw_x_data_label_lines = (bool)$dxdl;
        return TRUE;
    }

    /*
     * Set the main title text for the plot.
     */
    function SetTitle($which_title)
    {
        $this->title_txt = $which_title;
        return TRUE;
    }

    /*
     * Set the X axis title and position.
     */
    function SetXTitle($which_xtitle, $which_xpos = 'plotdown')
    {
        if ($which_xtitle == '')
            $which_xpos = 'none';

        $this->x_title_pos = $this->CheckOption($which_xpos, 'plotdown, plotup, both, none', __FUNCTION__);
        if (!$this->x_title_pos) return FALSE;
        $this->x_title_txt = $which_xtitle;
        return TRUE;
    }

    /*
     * Set the Y axis title and position.
     */
    function SetYTitle($which_ytitle, $which_ypos = 'plotleft')
    {
        if ($which_ytitle == '')
            $which_ypos = 'none';

        $this->y_title_pos = $this->CheckOption($which_ypos, 'plotleft, plotright, both, none', __FUNCTION__);
        if (!$this->y_title_pos) return FALSE;
        $this->y_title_txt = $which_ytitle;
        return TRUE;
    }

    /*
     * Set the size of the drop shadow for bar and pie charts.
     *   $which_s : Size of the drop shadow in pixels.
     */
    function SetShading($which_s)
    {
        $this->shading = (int)$which_s;
        return TRUE;
    }

    /*
     * Set the plot type (bars, points, ...)
     */
    function SetPlotType($which_pt)
    {
        $avail_plot_types = implode(', ', array_keys(PHPlot::$plots)); // List of known plot types
        $this->plot_type = $this->CheckOption($which_pt, $avail_plot_types, __FUNCTION__);
        return (boolean)$this->plot_type;
    }

    /*
     * Set the position of the X axis.
     *  $pos : Axis position in world coordinates (as an integer).
     */
    function SetXAxisPosition($pos='')
    {
        $this->x_axis_position = ($pos === '') ? $pos : (int)$pos;
        return TRUE;
    }

    /*
     * Set the position of the Y axis.
     *  $pos : Axis position in world coordinates (as an integer).
     */
    function SetYAxisPosition($pos='')
    {
        $this->y_axis_position = ($pos === '') ? $pos : (int)$pos;
        return TRUE;
    }

    /*
     * Enable or disable drawing of the X axis line.
     *  $draw : True to draw the axis (default if not called), False to suppress it.
     * This controls drawing of the axis line only, and not the ticks, labels, or grid.
     */
    function SetDrawXAxis($draw)
    {
        $this->suppress_x_axis = !$draw; // See DrawXAxis()
        return TRUE;
    }

    /*
     * Enable or disable drawing of the Y axis line.
     *  $draw : True to draw the axis (default if not called), False to suppress it.
     * This controls drawing of the axis line only, and not the ticks, labels, or grid.
     */
    function SetDrawYAxis($draw)
    {
        $this->suppress_y_axis = !$draw; // See DrawYAxis()
        return TRUE;
    }

    /*
     * Select linear or log scale for the X axis.
     */
    function SetXScaleType($which_xst)
    {
        $this->xscale_type = $this->CheckOption($which_xst, 'linear, log', __FUNCTION__);
        return (boolean)$this->xscale_type;
    }

    /*
     * Select linear or log scale for the Y axis.
     */
    function SetYScaleType($which_yst)
    {
        $this->yscale_type = $this->CheckOption($which_yst, 'linear, log',  __FUNCTION__);
        return (boolean)$this->yscale_type;
    }

    /*
     * Set the precision for numerically formatted X labels.
     *   $which_prec : Number of digits to display.
     * Note: This is equivalent to: SetXLabelType('data', $which_prec)
     */
    function SetPrecisionX($which_prec)
    {
        return $this->SetXLabelType('data', $which_prec);
    }

    /*
     * Set the precision for numerically formatted Y labels.
     *   $which_prec : Number of digits to display.
     * Note: This is equivalent to: SetYLabelType('data', $which_prec)
     */
    function SetPrecisionY($which_prec)
    {
        return $this->SetYLabelType('data', $which_prec);
    }

    /*
     * Set the line width (in pixels) for error bars.
     */
    function SetErrorBarLineWidth($which_seblw)
    {
        $this->error_bar_line_width = $which_seblw;
        return TRUE;
    }

    /*
     * Set the position for pie chart percentage labels.
     *   $which_blb : Real number between 0 and 1.
     *      Smaller values move the labels in towards the center.
     */
    function SetLabelScalePosition($which_blp)
    {
        $this->label_scale_position = $which_blp;
        return TRUE;
    }

    /*
     * Set the size (in pixels) of the "T" in error bars.
     */
    function SetErrorBarSize($which_ebs)
    {
        $this->error_bar_size = $which_ebs;
        return TRUE;
    }

    /*
     * Set the shape of the in error bars.
     *   $which_ebs : Error bar shape, 'tee' or 'line'.
     */
    function SetErrorBarShape($which_ebs)
    {
        $this->error_bar_shape = $this->CheckOption($which_ebs, 'tee, line', __FUNCTION__);
        return (boolean)$this->error_bar_shape;
    }

    /*
     * Synchronize the point shape and point size arrays.
     * This is called just before drawing any plot that needs 'points'.
     */
    protected function CheckPointParams()
    {
        // Make both point_shapes and point_sizes the same size, by padding the smaller.
        $ps = count($this->point_sizes);
        $pt = count($this->point_shapes);

        if ($ps < $pt) {
            $this->pad_array($this->point_sizes, $pt);
            $this->point_counts = $pt;
        } elseif ($ps > $pt) {
            $this->pad_array($this->point_shapes, $ps);
            $this->point_counts = $ps;
        } else {
            $this->point_counts = $ps;
        }

        // Note: PHPlot used to check and adjust point_sizes to be an even number here,
        // for all 'diamond' and 'triangle' shapes. The reason for this having been
        // lost, and the current maintainer seeing no sense it doing this for only
        // some shapes, the code has been removed. But see what DrawDot() does.
    }

    /*
     * Set the point shape for each data set.
     *   $which_pt : Array (or single value) of valid point shapes. See also DrawDot() for valid shapes.
     * The point shape and point sizes arrays are synchronized before drawing a graph
     * that uses points. See CheckPointParams()
     */
    function SetPointShapes($which_pt)
    {
        $this->point_shapes = $this->CheckOptionArray($which_pt, 'halfline, line, plus, cross, rect,'
                       . ' circle, dot, diamond, triangle, trianglemid, delta, yield, star, hourglass,'
                       . ' bowtie, target, box, home, up, down, none', __FUNCTION__);
        return !empty($this->point_shapes);
    }

    /*
     * Set the point size for point plots.
     *   $which_ps : Array (or single value) of point sizes in pixels.
     * The point shape and point sizes arrays are synchronized before drawing a graph
     * that uses points. See CheckPointParams()
     */
    function SetPointSizes($which_ps)
    {
        if (is_array($which_ps)) {
            // Use provided array:
            $this->point_sizes = $which_ps;
        } elseif (!is_null($which_ps)) {
            // Make the single value into an array:
            $this->point_sizes = array($which_ps);
        }
        return TRUE;
    }

    /*
     * Sets whether lines should be broken at missing data.
     *   $bl : True to break the lines, false to connect around missing data.
     * This only works with 'lines' and 'squared' plots.
     */
    function SetDrawBrokenLines($bl)
    {
        $this->draw_broken_lines = (bool)$bl;
        return TRUE;
    }

    /*
     * Set the data type, which defines the structure of the data array
     *  text-data: ('label', y1, y2, y3, ...)
     *  text-data-single: ('label', data), for some pie charts.
     *  data-data: ('label', x, y1, y2, y3, ...)
     *  data-data-error: ('label', x1, y1, e1+, e2-, y2, e2+, e2-, y3, e3+, e3-, ...)
     *  data-data-yx: ('label', y, x1, x2, x3, ..)
     *  text-data-yx: ('label', x1, x2, x3, ...)
     *  data-data-xyz: ('label', x, y1, z1, ...)
     */
    function SetDataType($which_dt)
    {
        //The next four lines are for past compatibility.
        if ($which_dt == 'text-linear') $which_dt = 'text-data';
        elseif ($which_dt == 'linear-linear') $which_dt = 'data-data';
        elseif ($which_dt == 'linear-linear-error') $which_dt = 'data-data-error';
        elseif ($which_dt == 'text-data-pie') $which_dt = 'text-data-single';

        $this->data_type = $this->CheckOption($which_dt, 'text-data, text-data-single, '.
                                                         'data-data, data-data-error, '.
                                                         'data-data-yx, text-data-yx, data-data-xyz',
                                                         __FUNCTION__);
        return (boolean)$this->data_type;
    }

    /*
     * Copy the array of data values, converting rows to numerical indexes.
     * Also validates that the array uses 0-based sequential integer indexes, and that each
     * array value (row) is another array. Other validation is deferred to CheckDataArray().
     */
    function SetDataValues($which_dv)
    {
        $this->num_data_rows = count($which_dv);
        $this->total_records = 0;
        $this->data = array();
        $this->num_recs = array();
        for ($i = 0; $i < $this->num_data_rows; $i++) {
            if (!isset($which_dv[$i]) || !is_array($which_dv[$i])) {
                return $this->PrintError("SetDataValues(): Invalid data array (row $i)");
            }
            $this->data[$i] = array_values($which_dv[$i]);   // convert to numerical indices.

            // Count size of each row, and total for the array.
            $this->total_records += $this->num_recs[$i] = count($this->data[$i]);
        }
        // This is the size of the widest row in the data array
        // Note records_per_group isn't used much anymore. See data_columns in CheckDataArray()
        $this->records_per_group = empty($this->num_recs) ? 0 : max($this->num_recs);
        return TRUE;
    }

    /*
     * Pad styles arrays for later use by plot drawing functions:
     * This removes the need for $max_data_colors, etc. and $color_index = $color_index % $max_data_colors
     * in DrawBars(), DrawLines(), etc.
     * The arrays are padded to data_columns which is the maximum number of data sets.
     * See CheckDataArray() for the calculation.
     */
    protected function PadArrays()
    {
        $this->pad_array($this->line_widths, $this->data_columns);
        $this->pad_array($this->line_styles, $this->data_columns);
        $this->pad_array($this->ndx_data_colors, $this->data_columns);
        $this->pad_array($this->ndx_data_border_colors, $this->data_columns);
        // Other data color arrays are handled in the Need*Colors() functions.

        return TRUE;
    }

    /*
     * Pads an array with itself. This only works on 0-based sequential integer indexed arrays.
     *    $arr : The array (or scalar) to pad. This argument is modified.
     *    $size : Minimum size of the resulting array.
     * If $arr is a scalar, it will be converted first to a single element array.
     * If $arr has at least $size elements, it is unchanged.
     * Otherwise, append elements of $arr to itself until it reaches $size elements.
     */
    protected function pad_array(&$arr, $size)
    {
        if (! is_array($arr)) {
            $arr = array($arr);
        }
        $n = count($arr);
        $base = 0;
        while ($n < $size) $arr[$n++] = $arr[$base++];
    }

    /*
     * Format a floating-point number.
     *   $number : A floating point number to format
     *   $decimals : Number of decimal places in the result
     *   Returns the formatted result.
     * This is like PHP's number_format, but uses class variables for separators.
     * The separators will default to locale-specific values, if available.
     */
    protected function number_format($number, $decimals=0)
    {
        if (!isset($this->decimal_point, $this->thousands_sep)) {
            // Load locale-specific values from environment, unless disabled:
            if (empty($this->locale_override))
                @setlocale(LC_ALL, '');
            // Fetch locale settings:
            $locale = @localeconv();
            if (isset($locale['decimal_point'], $locale['thousands_sep'])) {
                $this->decimal_point = $locale['decimal_point'];
                $this->thousands_sep = $locale['thousands_sep'];
            } else {
                // Locale information not available.
                $this->decimal_point = '.';
                $this->thousands_sep = ',';
            }
        }
        return number_format($number, $decimals, $this->decimal_point, $this->thousands_sep);
    }

    /*
     * Register a callback (hook) function
     *   $reason : A pre-defined name where a callback can be defined.
     *   $function : The name of a function to register for callback, or an instance/method
     *      pair in an array (see 'callbacks' in the PHP reference manual).
     *   $arg : Optional argument to supply to the callback function when it is triggered.
     *      (Often called "clientData")
     *   Returns True if the callback reason is valid, else False.
     */
    function SetCallback($reason, $function, $arg = NULL)
    {
        // Use array_key_exists because valid reason keys have NULL as value.
        if (!array_key_exists($reason, $this->callbacks))
            return FALSE;
        $this->callbacks[$reason] = array($function, $arg);
        return TRUE;
    }

    /*
     * Return the name of a function registered for callback. See SetCallBack.
     *   $reason - A pre-defined name where a callback can be defined.
     *   Returns the current callback function (name or array) for the given reason,
     *   or False if there was no active callback or the reason is not valid.
     * Note you can safely test the return value with a simple 'if', as
     * no valid function name evaluates to false.
     */
    function GetCallback($reason)
    {
        if (isset($this->callbacks[$reason]))
            return $this->callbacks[$reason][0];
        return FALSE;
    }

    /*
     * Un-register (remove) a function registered for callback.
     *   $reason - A pre-defined name where a callback can be defined.
     *   Returns: True if it was a valid callback reason, else False.
     * Note: Returns True whether or not there was a callback registered.
     */
    function RemoveCallback($reason)
    {
        if (!array_key_exists($reason, $this->callbacks))
            return FALSE;
        $this->callbacks[$reason] = NULL;
        return TRUE;
    }

    /*
     * Invoke a callback, if one is registered.
     * Accepts a variable number of arguments >= 1:
     *   $reason : A string naming the callback.
     *   ... : Zero or more additional arguments to be passed to the
     *         callback function, after the passthru argument:
     *           callback_function($image, $passthru, ...)
     *   Returns: whatever value (if any) was returned by the callback.
     */
    protected function DoCallback() // Note: Variable arguments
    {
        $args = func_get_args();
        $reason = $args[0];
        if (!isset($this->callbacks[$reason]))
            return;
        list($function, $args[0]) = $this->callbacks[$reason];
        array_unshift($args, $this->img);
        // Now args[] looks like: img, passthru, extra args...
        return call_user_func_array($function, $args);
    }

    /*
     * Allocate colors for the plot.
     * This is called by DrawGraph to allocate the colors needed for the plot.  Each selectable
     * color has already been validated, parsed into an array (r,g,b,a), and stored into a member
     * variable. Now the GD color indexes are assigned and stored into the ndx_*_color variables.
     * This is deferred here to avoid allocating unneeded colors and to avoid order dependencies,
     * especially with the transparent color.
     *
     * For drawing data elements, only the main data colors and border colors are allocated here.
     * Dark colors and error bar colors are allocated by Need*Color() functions.
     * (Data border colors default to just black, so there is no cost to always allocating.)
     *
     * Data color allocation works as follows. If there is a data_color callback, then allocate all
     * defined data colors (because the callback can use them however it wants). Otherwise, only allocate
     * the number of colors that will be used. This is the larger of the number of data sets and the
     * number of legend lines.
     */
    protected function SetColorIndexes()
    {
        $this->ndx_bg_color         = $this->GetColorIndex($this->bg_color); // Background first
        $this->ndx_plot_bg_color    = $this->GetColorIndex($this->plot_bg_color);
        if ($this->image_border_type != 'none') {
            $this->ndx_i_border         = $this->GetColorIndex($this->i_border);
            $this->ndx_i_border_dark    = $this->GetDarkColorIndex($this->i_border);
        }

        // Handle defaults for X and Y title colors.
        $this->ndx_title_color      = $this->GetColorIndex($this->title_color);
        if (empty($this->x_title_color)) {
            $this->ndx_x_title_color = $this->ndx_title_color;
        } else {
            $this->ndx_x_title_color = $this->GetColorIndex($this->x_title_color);
        }
        if (empty($this->y_title_color)) {
            $this->ndx_y_title_color = $this->ndx_title_color;
        } else {
            $this->ndx_y_title_color = $this->GetColorIndex($this->y_title_color);
        }

        $this->ndx_text_color       = $this->GetColorIndex($this->text_color);
        $this->ndx_grid_color       = $this->GetColorIndex($this->grid_color);
        $this->ndx_light_grid_color = $this->GetColorIndex($this->light_grid_color);
        $this->ndx_tick_color       = $this->GetColorIndex($this->tick_color);

        // Maximum number of data & border colors to allocate:
        if ($this->GetCallback('data_color')) {
            $n_data = count($this->data_colors); // Need all of them
            $n_border = count($this->data_border_colors);
        } else {
            $n_data = max($this->data_columns, empty($this->legend) ? 0 : count($this->legend));
            $n_border = $n_data; // One border color per data color
        }

        // Allocate main data colors. For other colors used for data, see the functions which follow.
        $this->ndx_data_colors = $this->GetColorIndexArray($this->data_colors, $n_data);
        $this->ndx_data_border_colors = $this->GetColorIndexArray($this->data_border_colors, $n_border);

        // Set up a color as transparent, if SetTransparentColor was used.
        if (!empty($this->transparent_color)) {
            imagecolortransparent($this->img, $this->GetColorIndex($this->transparent_color));
        }
    }

    /*
     * Allocate dark-shade data colors. Called if needed by graph drawing functions.
     */
    protected function NeedDataDarkColors()
    {
        // This duplicates the calculation in SetColorIndexes() for number of data colors to allocate.
        if ($this->GetCallback('data_color')) {
            $n_data = count($this->data_colors);
        } else {
            $n_data = max($this->data_columns, empty($this->legend) ? 0 : count($this->legend));
        }
        $this->ndx_data_dark_colors = $this->GetDarkColorIndexArray($this->data_colors, $n_data);
        $this->pad_array($this->ndx_data_dark_colors, $this->data_columns);
    }

    /*
     * Allocate error bar colors. Called if needed by graph drawing functions.
     */
    protected function NeedErrorBarColors()
    {
        // This is similar to the calculation in SetColorIndexes() for number of data colors to allocate.
        if ($this->GetCallback('data_color')) {
            $n_err = count($this->error_bar_colors);
        } else {
            $n_err = max($this->data_columns, empty($this->legend) ? 0 : count($this->legend));
        }
        $this->ndx_error_bar_colors = $this->GetColorIndexArray($this->error_bar_colors, $n_err);
        $this->pad_array($this->ndx_error_bar_colors, $this->data_columns);
    }

    /*
     * Determine if, and where, to draw Data Value Labels.
     *   $label_control : Label position control. Either x_data_label_pos or y_data_label_pos.
     *   &$x_adj, &$y_adj : Returns X,Y adjustments (offset in pixels) to the text position.
     *   &$h_align, &$v_align : Returns horizontal and vertical alignment for the label.
     *      The above 4 argument values should be passed to DrawDataValueLabel()
     * Returns True if data value labels should be drawn (based on $label_control), else False.
     * This is used for plot types other than bars/stackedbars (which have their own way of doing it).
     * It uses two member variables (unset by default): data_value_label_angle and data_value_label_distance
     * to define the vector to the label. Default is 90 degrees at 5 pixels.
     */
    protected function CheckDataValueLabels($label_control, &$x_adj, &$y_adj, &$h_align, &$v_align)
    {
        if ($label_control != 'plotin')
            return FALSE; // No data value labels
        $angle = deg2rad(isset($this->data_value_label_angle) ? $this->data_value_label_angle : 90);
        $radius = isset($this->data_value_label_distance) ? $this->data_value_label_distance : 5;
        $cos = cos($angle);
        $sin = sin($angle);
        $x_adj = (int)($radius * $cos);
        $y_adj = -(int)($radius * $sin); // Y is reversed in device coordinates

        // Choose from 8 (not 9, center/center can't happen) text alignments based on angle:
        if ($sin >= 0.383) $v_align = 'bottom'; // 0.383 = sin(360deg / 16)
        elseif ($sin >= -0.383) $v_align = 'center';
        else $v_align = 'top';
        if ($cos >= 0.383) $h_align = 'left';
        elseif ($cos >= -0.383) $h_align = 'center';
        else $h_align = 'right';
        return TRUE;
    }

//////////////////////////////////////////////////////////
///////////         DATA ANALYSIS, SCALING AND TRANSLATION
//////////////////////////////////////////////////////////

    /*
     * Analyzes the data array and calculates the minimum and maximum values.
     * In this function, IV refers to the independent variable, and DV the dependent variable.
     * For most plots, IV is X and DV is Y. For swapped X/Y plots, IV is Y and DV is X.
     * At the end of the function, IV and DV ranges get assigned into X or Y.
     *
     * The data type mostly determines the data array structure, but some plot types do special
     * things such as sum the values in a row. This information is in the plots[] array.
     *
     * This calculates min_x, max_x, min_y, and max_y. It also calculates two arrays
     * data_min[] and data_max[] with per-row min and max values. These are used for
     * data label lines. For normal (unswapped) data, these are the Y range for each X.
     * For swapped X/Y data, they are the X range for each Y.
     * For X/Y/Z plots, it also calculates min_z and max_z.
     */
    protected function FindDataLimits()
    {
        // Does this plot type need special processing of the data values?
        $sum_vals = !empty(PHPlot::$plots[$this->plot_type]['sum_vals']); // Add up values in each row
        $abs_vals = !empty(PHPlot::$plots[$this->plot_type]['abs_vals']); // Take absolute values

        // These need to be initialized in case there are multiple plots and missing data points.
        $this->data_min = array();
        $this->data_max = array();

        // Independent values are in the data array or assumed?
        if ($this->datatype_implied) {
            $all_iv = array(0, $this->num_data_rows - 1);
        } else {
            $all_iv = array();
        }
        // For X/Y/Z plots, make sure these are not left over from a previous plot.
        if ($this->datatype_yz) {
            unset($this->min_z);
            unset($this->max_z);
        }

        // Process all rows of data:
        for ($i = 0; $i < $this->num_data_rows; $i++) {
            $n_vals = $this->num_recs[$i];
            $j = 1; // Skips label at [0]

            if (!$this->datatype_implied) {
                $all_iv[] = (double)$this->data[$i][$j++];
            }

            if ($sum_vals) {
                $all_dv = array(0, 0); // One limit is 0, other calculated below
            } else {
                $all_dv = array();
            }
            while ($j < $n_vals) {
                if (is_numeric($this->data[$i][$j])) {
                    $val = (double)$this->data[$i][$j++];

                    if ($this->datatype_error_bars) {
                        $all_dv[] = $val + (double)$this->data[$i][$j++];
                        $all_dv[] = $val - (double)$this->data[$i][$j++];
                    } else {
                        if ($abs_vals) {
                            $val = abs($val); // Use absolute values
                        }
                        if ($sum_vals) {
                            $all_dv[1] += $val;  // Sum of values
                        } else {
                            $all_dv[] = $val; // List of all values
                        }
                        if ($this->datatype_yz) {
                            $z = $this->data[$i][$j++]; // Note Z is required if Y is present.
                            if (!isset($this->min_z) || $z < $this->min_z) $this->min_z = $z;
                            if (!isset($this->max_z) || $z > $this->max_z) $this->max_z = $z;
                        }
                    }
                } else {    // Missing DV value
                  $j++;
                  if ($this->datatype_error_bars) $j += 2;
                  elseif ($this->datatype_yz) $j++;
                }
            }
            if (!empty($all_dv)) {
                $this->data_min[$i] = min($all_dv);  // Store per-row DV range
                $this->data_max[$i] = max($all_dv);
            }
        }

        if ($this->datatype_swapped_xy) {
            // Assign min and max for swapped X/Y plots: IV=Y and DV=X
            $this->min_y = min($all_iv);
            $this->max_y = max($all_iv);
            if (empty($this->data_min)) { // Guard against regressive case: No X at all
                $this->min_x = 0;
                $this->max_x = 0;
            } else {
                $this->min_x = min($this->data_min);  // Store global X range
                $this->max_x = max($this->data_max);
            }
        } else {
            // Assign min and max for normal plots: IV=X and DV=Y
            $this->min_x = min($all_iv);
            $this->max_x = max($all_iv);
            if (empty($this->data_min)) { // Guard against regressive case: No Y at all
                $this->min_y = 0;
                $this->max_y = 0;
            } else {
                $this->min_y = min($this->data_min);  // Store global Y range
                $this->max_y = max($this->data_max);
            }
        }
        // For X/Y/Z plots, make sure these are set. If there are no valid data values,
        // they will be unset, so set them here to prevent undefined property warnings.
        if ($this->datatype_yz && !isset($this->min_z)) {   // Means max_z is also unset
            $this->max_z = $this->min_z = 0; // Actual values do not matter.
        }

        if ($this->GetCallback('debug_scale')) {
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'min_x' => $this->min_x, 'min_y' => $this->min_y,
                'max_x' => $this->max_x, 'max_y' => $this->max_y, 
                'min_z' => isset($this->min_z) ? $this->min_z : '',
                'max_z' => isset($this->max_z) ? $this->max_z : ''));
        }
        return TRUE;
    }

    /*
     * Calculates image margins on the fly from title positions and sizes,
     * and tick labels positions and sizes.
     *
     * A picture of the locations of elements and spacing can be found in the
     * PHPlot Reference Manual.
     *
     * Calculates the following (class variables unless noted):
     *
     * Plot area margins (see note below):
     *     y_top_margin
     *     y_bot_margin
     *     x_left_margin
     *     x_right_margin
     *
     * Title sizes (these are now local, not class variables, since they are not used elsewhere):
     *     title_height : Height of main title
     *     x_title_height : Height of X axis title, 0 if no X title
     *     y_title_width : Width of Y axis title, 0 if no Y title
     *
     * Tick/Data label offsets, relative to plot_area:
     *     x_label_top_offset, x_label_bot_offset, x_label_axis_offset
     *     y_label_left_offset, y_label_right_offset, y_label_axis_offset
     *
     * Title offsets, relative to plot area:
     *     x_title_top_offset, x_title_bot_offset
     *     y_title_left_offset, y_title_left_offset
     *     title_offset (for main title, relative to image edge)
     *
     *  Note: The margins are calculated, but not stored, if margins or plot area were
     *  set by the user with SetPlotAreaPixels or SetMarginsPixels. The margin
     *  calculation is mixed in with the offset variables, so it doesn't seem worth the
     *  trouble to separate them.
     *
     * If the $maximize argument is true, we use the full image size, minus safe_margin
     * and main title, for the plot. This is for pie charts which have no axes or X/Y titles.
     */
    protected function CalcMargins($maximize)
    {
        // This is the line-to-line or line-to-text spacing:
        $gap = $this->safe_margin;
        // Initial margin on each side takes into account a possible image border.
        // For compatibility, if border is 1 or 2, don't increase the margins.
        $base_margin = max($gap, $this->GetImageBorderWidth() + 3);
        $this->title_offset = $base_margin;  // For use in DrawTitle

        // Minimum margin on each side. This reduces the chance that the
        // right-most tick label (for example) will run off the image edge
        // if there are no titles on that side.
        $min_margin = 2 * $gap + $base_margin;

        // Calculate the title sizes:
        list($unused, $title_height) = $this->SizeText($this->fonts['title'], 0, $this->title_txt);
        list($unused, $x_title_height) = $this->SizeText($this->fonts['x_title'], 0, $this->x_title_txt);
        list($y_title_width, $unused) = $this->SizeText($this->fonts['y_title'], 90, $this->y_title_txt);

        // Special case for maximum area usage with no X/Y titles or labels, only main title:
        if ($maximize) {
            if (!isset($this->x_left_margin))
                $this->x_left_margin = $base_margin;
            if (!isset($this->x_right_margin))
                $this->x_right_margin = $base_margin;
            if (!isset($this->y_top_margin)) {
                $this->y_top_margin = $base_margin;
                if ($title_height > 0)
                    $this->y_top_margin += $title_height + $gap;
            }
            if (!isset($this->y_bot_margin))
                $this->y_bot_margin = $base_margin;

            return TRUE;
        }

        // Make local variables for these. (They get used a lot and I'm tired of this, this, this.)
        $x_tick_label_pos = $this->x_tick_label_pos;
        $x_data_label_pos = $this->x_data_label_pos;
        $x_tick_pos       = $this->x_tick_pos;
        $x_tick_len       = $this->x_tick_length;
        $y_tick_label_pos = $this->y_tick_label_pos;
        $y_tick_pos       = $this->y_tick_pos;
        $y_tick_len       = $this->y_tick_length;
        $y_data_label_pos = $this->y_data_label_pos;

        // For X/Y tick and label position of 'xaxis' or 'yaxis', determine if the axis happens to be
        // on an edge of a plot. If it is, we need to account for the margins there.
        if ($this->x_axis_position <= $this->plot_min_y)
            $x_axis_pos = 'bottom';
        elseif ($this->x_axis_position >= $this->plot_max_y)
            $x_axis_pos = 'top';
        else
            $x_axis_pos = 'none';
        if ($this->y_axis_position <= $this->plot_min_x)
            $y_axis_pos = 'left';
        elseif ($this->y_axis_position >= $this->plot_max_x)
            $y_axis_pos = 'right';
        else
            $y_axis_pos = 'none';

        // Calculate the heights for X tick and data labels, and the max (used if they are overlaid):
        $x_data_label_height = ($x_data_label_pos == 'none') ? 0 : $this->CalcMaxDataLabelSize('x');
        $x_tick_label_height = ($x_tick_label_pos == 'none') ? 0 : $this->CalcMaxTickLabelSize('x');
        $x_max_label_height = max($x_data_label_height, $x_tick_label_height);

        // Calculate the space needed above and below the plot for X tick and X data labels:

        // Above the plot:
        $tick_labels_above = ($x_tick_label_pos == 'plotup' || $x_tick_label_pos == 'both'
                          || ($x_tick_label_pos == 'xaxis' && $x_axis_pos == 'top'));
        $data_labels_above = ($x_data_label_pos == 'plotup' || $x_data_label_pos == 'both');
        if ($tick_labels_above) {
            if ($data_labels_above) {
                $label_height_above = $x_max_label_height;
            } else {
                $label_height_above = $x_tick_label_height;
            }
        } elseif ($data_labels_above) {
            $label_height_above = $x_data_label_height;
        } else {
            $label_height_above = 0;
        }

        // Below the plot:
        $tick_labels_below = ($x_tick_label_pos == 'plotdown' || $x_tick_label_pos == 'both'
                          || ($x_tick_label_pos == 'xaxis' && $x_axis_pos == 'bottom'));
        $data_labels_below = ($x_data_label_pos == 'plotdown' || $x_data_label_pos == 'both');
        if ($tick_labels_below) {
            if ($data_labels_below) {
                $label_height_below = $x_max_label_height;
            } else {
                $label_height_below = $x_tick_label_height;
            }
        } elseif ($data_labels_below) {
            $label_height_below = $x_data_label_height;
        } else {
            $label_height_below = 0;
        }

        // Calculate the width for Y tick and data labels, if on, and the max:
        // Note CalcMaxDataLabelSize('y') returns 0 except for swapped X/Y plots.
        $y_data_label_width = ($y_data_label_pos == 'none') ? 0 : $this->CalcMaxDataLabelSize('y');
        $y_tick_label_width = ($y_tick_label_pos == 'none') ? 0 : $this->CalcMaxTickLabelSize('y');
        $y_max_label_width = max($y_data_label_width, $y_tick_label_width);

        // Calculate the space needed left and right of the plot for Y tick and Y data labels:
        // (Y data labels here are for swapped X/Y plots such has horizontal bars)

        // Left of the plot:
        $tick_labels_left = ($y_tick_label_pos == 'plotleft' || $y_tick_label_pos == 'both'
                         || ($y_tick_label_pos == 'yaxis' && $y_axis_pos == 'left'));
        $data_labels_left = ($y_data_label_pos == 'plotleft' || $y_data_label_pos == 'both');
        if ($tick_labels_left) {
            if ($data_labels_left) {
                $label_width_left = $y_max_label_width;
            } else {
                $label_width_left = $y_tick_label_width;
            }
        } elseif ($data_labels_left) {
            $label_width_left = $y_data_label_width;
        } else {
            $label_width_left = 0;
        }

        // Right of the plot:
        $tick_labels_right = ($y_tick_label_pos == 'plotright' || $y_tick_label_pos == 'both'
                          || ($y_tick_label_pos == 'yaxis' && $y_axis_pos == 'right'));
        $data_labels_right = ($y_data_label_pos == 'plotright' || $y_data_label_pos == 'both');
        if ($tick_labels_right) {
            if ($data_labels_right) {
                $label_width_right = $y_max_label_width;
            } else {
                $label_width_right = $y_tick_label_width;
            }
        } elseif ($data_labels_right) {
            $label_width_right = $y_data_label_width;
        } else {
            $label_width_right = 0;
        }

        ///////// Calculate margins:

        // Calculating Top and Bottom margins:
        // y_top_margin: Main title, Upper X title, X ticks and tick labels, and X data labels:
        // y_bot_margin: Lower title, ticks and tick labels, and data labels:
        $top_margin = $base_margin;
        $bot_margin = $base_margin;
        $this->x_title_top_offset = $gap;
        $this->x_title_bot_offset = $gap;

        // Space for main title?
        if ($title_height > 0)
            $top_margin += $title_height + $gap;

        // Space for X Title?
        if ($x_title_height > 0) {
            $pos = $this->x_title_pos;
            if ($pos == 'plotup' || $pos == 'both')
                $top_margin += $x_title_height + $gap;
            if ($pos == 'plotdown' || $pos == 'both')
                $bot_margin += $x_title_height + $gap;
        }

        // Space for X Labels above the plot?
        if ($label_height_above > 0) {
            $top_margin += $label_height_above + $gap;
            $this->x_title_top_offset += $label_height_above + $gap;
        }

        // Space for X Labels below the plot?
        if ($label_height_below > 0) {
            $bot_margin += $label_height_below + $gap;
            $this->x_title_bot_offset += $label_height_below + $gap;
        }

        // Space for X Ticks above the plot?
        if ($x_tick_pos == 'plotup' || $x_tick_pos == 'both'
           || ($x_tick_pos == 'xaxis' && $x_axis_pos == 'top')) {
            $top_margin += $x_tick_len;
            $this->x_label_top_offset = $x_tick_len + $gap;
            $this->x_title_top_offset += $x_tick_len;
        } else {
            // No X Ticks above the plot:
            $this->x_label_top_offset = $gap;
        }

        // Space for X Ticks below the plot?
        if ($x_tick_pos == 'plotdown' || $x_tick_pos == 'both'
           || ($x_tick_pos == 'xaxis' && $x_axis_pos == 'bottom')) {
            $bot_margin += $x_tick_len;
            $this->x_label_bot_offset = $x_tick_len + $gap;
            $this->x_title_bot_offset += $x_tick_len;
        } else {
            // No X Ticks below the plot:
            $this->x_label_bot_offset = $gap;
        }
        // Label offsets for on-axis ticks:
        if ($x_tick_pos == 'xaxis') {
            $this->x_label_axis_offset = $x_tick_len + $gap;
        } else {
            $this->x_label_axis_offset = $gap;
        }

        // Calculating Left and Right margins:
        // x_left_margin: Left Y title, Y ticks and tick labels:
        // x_right_margin: Right Y title, Y ticks and tick labels:
        $left_margin = $base_margin;
        $right_margin = $base_margin;
        $this->y_title_left_offset = $gap;
        $this->y_title_right_offset = $gap;

        // Space for Y Title?
        if ($y_title_width > 0) {
            $pos = $this->y_title_pos;
            if ($pos == 'plotleft' || $pos == 'both')
                $left_margin += $y_title_width + $gap;
            if ($pos == 'plotright' || $pos == 'both')
                $right_margin += $y_title_width + $gap;
        }

        // Space for Y Labels left of the plot?
        if ($label_width_left > 0) {
            $left_margin += $label_width_left + $gap;
            $this->y_title_left_offset += $label_width_left + $gap;
        }

        // Space for Y Labels right of the plot?
        if ($label_width_right > 0) {
            $right_margin += $label_width_right + $gap;
            $this->y_title_right_offset += $label_width_right + $gap;
        }

        // Space for Y Ticks left of plot?
        if ($y_tick_pos == 'plotleft' || $y_tick_pos == 'both'
           || ($y_tick_pos == 'yaxis' && $y_axis_pos == 'left')) {
            $left_margin += $y_tick_len;
            $this->y_label_left_offset = $y_tick_len + $gap;
            $this->y_title_left_offset += $y_tick_len;
        } else {
            // No Y Ticks left of plot:
            $this->y_label_left_offset = $gap;
        }

        // Space for Y Ticks right of plot?
        if ($y_tick_pos == 'plotright' || $y_tick_pos == 'both'
           || ($y_tick_pos == 'yaxis' && $y_axis_pos == 'right')) {
            $right_margin += $y_tick_len;
            $this->y_label_right_offset = $y_tick_len + $gap;
            $this->y_title_right_offset += $y_tick_len;
        } else {
            // No Y Ticks right of plot:
            $this->y_label_right_offset = $gap;
        }

        // Label offsets for on-axis ticks:
        if ($x_tick_pos == 'yaxis') {
            $this->y_label_axis_offset = $y_tick_len + $gap;
        } else {
            $this->y_label_axis_offset = $gap;
        }

        // Apply the minimum margins and store in the object.
        // Do not set margins which were user-defined (see note at top of function).
        if (!isset($this->y_top_margin))
            $this->y_top_margin = max($min_margin, $top_margin);
        if (!isset($this->y_bot_margin))
            $this->y_bot_margin = max($min_margin, $bot_margin);
        if (!isset($this->x_left_margin))
            $this->x_left_margin = max($min_margin, $left_margin);
        if (!isset($this->x_right_margin))
            $this->x_right_margin = max($min_margin, $right_margin);

        if ($this->GetCallback('debug_scale')) {
            // (Too bad compact() doesn't work on class member variables...)
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'label_height_above' => $label_height_above,
                'label_height_below' => $label_height_below,
                'label_width_left' => $label_width_left,
                'label_width_right' => $label_width_right,
                'x_tick_len' => $x_tick_len,
                'y_tick_len' => $y_tick_len,
                'x_left_margin' => $this->x_left_margin,
                'x_right_margin' => $this->x_right_margin,
                'y_top_margin' => $this->y_top_margin,
                'y_bot_margin' => $this->y_bot_margin,
                'x_label_top_offset' => $this->x_label_top_offset,
                'x_label_bot_offset' => $this->x_label_bot_offset,
                'y_label_left_offset' => $this->y_label_left_offset,
                'y_label_right_offset' => $this->y_label_right_offset,
                'x_title_top_offset' => $this->x_title_top_offset,
                'x_title_bot_offset' => $this->x_title_bot_offset,
                'y_title_left_offset' => $this->y_title_left_offset,
                'y_title_right_offset' => $this->y_title_right_offset));
        }

        return TRUE;
    }

    /*
     * Calculate the plot area (device coordinates) from the margins.
     * (This used to be part of SetPlotAreaPixels.)
     * The margins might come from SetMarginsPixels, SetPlotAreaPixels,
     * or CalcMargins.
     */
    protected function CalcPlotAreaPixels()
    {
        $this->plot_area = array($this->x_left_margin, $this->y_top_margin,
                                 $this->image_width - $this->x_right_margin,
                                 $this->image_height - $this->y_bot_margin);
        $this->plot_area_width = $this->plot_area[2] - $this->plot_area[0];
        $this->plot_area_height = $this->plot_area[3] - $this->plot_area[1];

        $this->DoCallback('debug_scale', __FUNCTION__, $this->plot_area);
        return TRUE;
    }

    /*
     * Set the margins in pixels (left, right, top, bottom)
     * This determines the plot area, equivalent to SetPlotAreaPixels().
     * Deferred calculations now occur in CalcPlotAreaPixels().
     */
    function SetMarginsPixels($which_lm = NULL, $which_rm = NULL, $which_tm = NULL, $which_bm = NULL)
    {
        $this->x_left_margin = $which_lm;
        $this->x_right_margin = $which_rm;
        $this->y_top_margin = $which_tm;
        $this->y_bot_margin = $which_bm;

        return TRUE;
    }

    /*
     * Sets the limits for the plot area.
     * This stores the margins, not the area. That may seem odd, but
     * the idea is to make SetPlotAreaPixels and SetMarginsPixels two
     * ways to accomplish the same thing, and the deferred calculations
     * in CalcMargins and CalcPlotAreaPixels don't need to know which
     * was used.
     *   (x1, y1) - Upper left corner of the plot area
     *   (x2, y2) - Lower right corner of the plot area
     */
    function SetPlotAreaPixels($x1 = NULL, $y1 = NULL, $x2 = NULL, $y2 = NULL)
    {
        $this->x_left_margin = $x1;
        if (isset($x2)) $this->x_right_margin = $this->image_width - $x2;
        else unset($this->x_right_margin);
        $this->y_top_margin = $y1;
        if (isset($y2)) $this->y_bot_margin = $this->image_height - $y2;
        else unset($this->y_bot_margin);

        return TRUE;
    }

    /*
     * Calculate the World Coordinate limits of the plot area.
     * This goes with SetPlotAreaWorld, but the calculations are
     * deferred until the graph is being drawn.
     * Uses and sets: plot_min_x, plot_max_x, plot_min_y, plot_max_y
     * These can be user-supplied or NULL to auto-calculate.
     * Pre-requisites: FindDataLimits() calculates min_x, max_x, min_y, max_y
     * which are the limits of the data to be plotted.
     *
     * The general method is this:
     *   If any part of the range is user-defined (via SetPlotAreaWorld),
     *      use the user-defined value.
     *   Else, if this is an implicitly-defined independent variable,
     *      use the fixed range of 0 to (max+1).
     *   Else, if this is an explicitly-defined independent variable,
     *      use the exact data range (min to max).
     *   Else, this is the dependent variable, so define a range which
     *      includes and exceeds the data range by a bit.
     */
    protected function CalcPlotAreaWorld()
    {
        // Data array omits X or Y?
        $implied_x = $this->datatype_implied && !$this->datatype_swapped_xy;
        $implied_y = $this->datatype_implied && $this->datatype_swapped_xy;

        if (isset($this->plot_min_x) && $this->plot_min_x !== '')
            $xmin = $this->plot_min_x; // Use user-provided value
        elseif ($implied_x)
            $xmin = 0;          // Implied X starts at zero
        elseif ($this->datatype_swapped_xy)
            // If X is the dependent variable, leave some room below.
            $xmin = floor($this->min_x - abs($this->min_x) * 0.1);
        else
            $xmin = $this->min_x;  // Otherwise just start at the min data X

        if (isset($this->plot_max_x) && $this->plot_max_x !== '')
            $xmax = $this->plot_max_x; // Use user-provided value
        elseif ($implied_x)
            $xmax = $this->max_x + 1; // Implied X ends after last value
        elseif ($this->datatype_swapped_xy)
            // If X is the dependent variable, leave some room above.
            $xmax = ceil($this->max_x + abs($this->max_x) * 0.1);
        else
            $xmax = $this->max_x; // Otherwise just end at the max data X

        if (isset($this->plot_min_y) && $this->plot_min_y !== '')
            $ymin = $this->plot_min_y;  // Use user-provided value
        elseif ($implied_y)
            $ymin = 0;    // Implied Y starts at zero
        elseif ($this->datatype_swapped_xy)
            $ymin = $this->min_y; // Start at min data Y
        else
            // If Y is the dependent variable, leave some room below.
            $ymin = floor($this->min_y - abs($this->min_y) * 0.1);

        if (isset($this->plot_max_y) && $this->plot_max_y !== '')
            $ymax = $this->plot_max_y; // Use user-provided value
        elseif ($implied_y)
            $ymax = $this->max_y + 1; // Implied Y ends after last value
        elseif ($this->datatype_swapped_xy)
            $ymax = $this->max_y;  // End at max data Y
        else
            // If Y is the dependent variable, leave some room above.
            $ymax = ceil($this->max_y + abs($this->max_y) * 0.1);

        // Error checking

        if ($ymin == $ymax)
            $ymax++;
        if ($xmin == $xmax)
            $xmax++;

        if ($this->yscale_type == 'log') {
            if ($ymin <= 0) {
                $ymin = 1;
            }
            if ($ymax <= 0) {
                // Note: Error messages reference the user function, not this function.
                return $this->PrintError('SetPlotAreaWorld(): Log plots need data greater than 0');
            }
        }

        if ($ymax <= $ymin) {
            return $this->PrintError('SetPlotAreaWorld(): Error in data - max not greater than min');
        }

        $this->plot_min_x = $xmin;
        $this->plot_max_x = $xmax;
        $this->plot_min_y = $ymin;
        $this->plot_max_y = $ymax;
        if ($this->GetCallback('debug_scale')) {
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'plot_min_x' => $this->plot_min_x, 'plot_min_y' => $this->plot_min_y,
                'plot_max_x' => $this->plot_max_x, 'plot_max_y' => $this->plot_max_y));
        }
        return TRUE;
    }

    /*
     * Stores the desired World Coordinate range of the plot.
     * The user calls this to force one or more of the range limits to
     * specific values. Anything not set will be calculated in CalcPlotAreaWorld().
     */
    function SetPlotAreaWorld($xmin=NULL, $ymin=NULL, $xmax=NULL, $ymax=NULL)
    {
        $this->plot_min_x = $xmin;
        $this->plot_max_x = $xmax;
        $this->plot_min_y = $ymin;
        $this->plot_max_y = $ymax;
        return TRUE;
    }

    /*
     * Calculate the width (or height) of bars for bar plots.
     *   $stacked : If true, this is a stacked bar plot (1 bar per group).
     *   $verticals : If false, this is a horizontal bar plot.
     * This calculates:
     *    record_bar_width : Allocated width for each bar (including gaps)
     *    actual_bar_width : Actual drawn width of each bar
     *    bar_adjust_gap  : Gap on each side of each bar (0 if they touch)
     * For the case $verticals=False, horizontal bars are being drawn,
     * but the same variable names are used. Think of "bar_width" as being
     * the width if you are standing on the Y axis looking towards positive X.
     */
    protected function CalcBarWidths($stacked, $verticals)
    {
        // group_width is the width of a group, including padding
        if ($verticals) {
            $group_width = $this->plot_area_width / $this->num_data_rows;
        } else {
            $group_width = $this->plot_area_height / $this->num_data_rows;
        }

        // Actual number of bar spaces in the group. This includes the drawn bars, and
        // 'bar_extra_space'-worth of extra bars.
        if ($stacked) {
            $num_spots = 1 + $this->bar_extra_space;
        } else {
            $num_spots = $this->data_columns + $this->bar_extra_space;
        }

        // record_bar_width is the width of each bar's allocated area.
        // If bar_width_adjust=1 this is the width of the bar, otherwise
        // the bar is centered inside record_bar_width.
        // The equation is:
        //   group_frac_width * group_width = record_bar_width * num_spots
        $this->record_bar_width = $this->group_frac_width * $group_width / $num_spots;

        // Note that the extra space due to group_frac_width and bar_extra_space will be
        // evenly divided on each side of the group: the drawn bars are centered in the group.

        // Within each bar's allocated space, if bar_width_adjust=1 the bar fills the
        // space, otherwise it is centered.
        // This is the actual drawn bar width:
        $this->actual_bar_width = $this->record_bar_width * $this->bar_width_adjust;
        // This is the gap on each side of the bar (0 if bar_width_adjust=1):
        $this->bar_adjust_gap = ($this->record_bar_width - $this->actual_bar_width) / 2;

        if ($this->GetCallback('debug_scale')) {
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'record_bar_width' => $this->record_bar_width,
                'actual_bar_width' => $this->actual_bar_width,
                'bar_adjust_gap' => $this->bar_adjust_gap));
        }
        return TRUE;
    }

    /*
     * Calculate X and Y Axis Positions, world coordinates.
     * This needs the min/max x/y range set by CalcPlotAreaWorld.
     * It adjusts or sets x_axis_position and y_axis_position per the data.
     * Empty string means the values need to be calculated; otherwise they
     * are supplied but need to be validated against the World area.
     *
     * Note: This used to be in CalcTranslation, but CalcMargins needs it too.
     * This does not calculate the pixel values of the axes. That happens in
     * CalcTranslation, after scaling is set up (which has to happen after
     * margins are set up).
     *
     * For vertical plots, the X axis defaults to Y=0 if that is inside the plot range, else whichever
     * of the top or bottom that has the smallest absolute value (that is, the value closest to 0).
     * The Y axis defaults to the left edge. For horizontal plots, the axis roles and defaults are switched.
     */
    protected function CalcAxisPositions()
    {
        // Validate user-provided X axis position, or calculate a default if not provided:
        if ($this->x_axis_position !== '') {
            // Force user-provided X axis position to be within the plot range:
            $this->x_axis_position = min(max($this->plot_min_y, $this->x_axis_position), $this->plot_max_y);
        } elseif ($this->yscale_type == 'log') {
            // Always use 1 for X axis position on log scale plots.
            $this->x_axis_position = 1;
        } elseif ($this->datatype_swapped_xy || $this->plot_min_y > 0) {
            // Horizontal plot, or Vertical Plot with all Y > 0: Place X axis on the bottom.
            $this->x_axis_position = $this->plot_min_y;
        } elseif ($this->plot_max_y < 0) {
            // Vertical plot with all Y < 0, so place the X axis at the top.
            $this->x_axis_position = $this->plot_max_y;
        } else {
            // Vertical plot range includes Y=0, so place X axis at 0.
            $this->x_axis_position = 0;
        }

        // Validate user-provided Y axis position, or calculate a default if not provided:
        if ($this->y_axis_position !== '') {
            // Force user-provided Y axis position to be within the plot range:
            $this->y_axis_position = min(max($this->plot_min_x, $this->y_axis_position), $this->plot_max_x);
        } elseif ($this->xscale_type == 'log') {
            // Always use 1 for Y axis position on log scale plots.
            $this->y_axis_position = 1;
        } elseif (!$this->datatype_swapped_xy || $this->plot_min_x > 0) {
            // Vertical plot, or Horizontal Plot with all X > 0: Place Y axis on left side.
            $this->y_axis_position = $this->plot_min_x;
        } elseif ($this->plot_max_x < 0) {
            // Horizontal plot with all X < 0, so place the Y axis on the right side.
            $this->y_axis_position = $this->plot_max_x;
        } else {
            // Horizontal plot range includes X=0: place Y axis at 0.
            $this->y_axis_position = 0;
        }

        if ($this->GetCallback('debug_scale')) {
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'x_axis_position' => $this->x_axis_position,
                'y_axis_position' => $this->y_axis_position));
        }

        return TRUE;
    }

    /*
     * Calculates scaling stuff...
     */
    protected function CalcTranslation()
    {
        if ($this->plot_max_x - $this->plot_min_x == 0) { // Check for div by 0
            $this->xscale = 0;
        } else {
            if ($this->xscale_type == 'log') {
                $this->xscale = $this->plot_area_width /
                                (log10($this->plot_max_x) - log10($this->plot_min_x));
            } else {
                $this->xscale = $this->plot_area_width / ($this->plot_max_x - $this->plot_min_x);
            }
        }

        if ($this->plot_max_y - $this->plot_min_y == 0) { // Check for div by 0
            $this->yscale = 0;
        } else {
            if ($this->yscale_type == 'log') {
                $this->yscale = $this->plot_area_height /
                                (log10($this->plot_max_y) - log10($this->plot_min_y));
            } else {
                $this->yscale = $this->plot_area_height / ($this->plot_max_y - $this->plot_min_y);
            }
        }
        // GD defines x = 0 at left and y = 0 at TOP so -/+ respectively
        if ($this->xscale_type == 'log') {
            $this->plot_origin_x = $this->plot_area[0] - ($this->xscale * log10($this->plot_min_x) );
        } else {
            $this->plot_origin_x = $this->plot_area[0] - ($this->xscale * $this->plot_min_x);
        }
        if ($this->yscale_type == 'log') {
            $this->plot_origin_y = $this->plot_area[3] + ($this->yscale * log10($this->plot_min_y));
        } else {
            $this->plot_origin_y = $this->plot_area[3] + ($this->yscale * $this->plot_min_y);
        }

        // Convert axis positions to device coordinates:
        $this->y_axis_x_pixels = $this->xtr($this->y_axis_position);
        $this->x_axis_y_pixels = $this->ytr($this->x_axis_position);

        if ($this->GetCallback('debug_scale')) {
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'xscale' => $this->xscale, 'yscale' => $this->yscale,
                'plot_origin_x' => $this->plot_origin_x, 'plot_origin_y' => $this->plot_origin_y,
                'y_axis_x_pixels' => $this->y_axis_x_pixels,
                'x_axis_y_pixels' => $this->x_axis_y_pixels));
        }

        return TRUE;
    }

    /*
     * Translate X world coordinate into pixel coordinate
     * See CalcTranslation() for calculation of xscale.
     * Note: This function should be 'protected', but is left public for historical reasons.
     * See GetDeviceXY() for a preferred public method.
     */
    function xtr($x_world)
    {
        if ($this->xscale_type == 'log') {
            $x_pixels = $this->plot_origin_x + log10($x_world) * $this->xscale ;
        } else {
            $x_pixels = $this->plot_origin_x + $x_world * $this->xscale ;
        }
        return round($x_pixels);
    }

    /*
     * Translate Y world coordinate into pixel coordinate.
     * See CalcTranslation() for calculation of yscale.
     * Note: This function should be 'protected', but is left public for historical reasons.
     * See GetDeviceXY() for a preferred public method.
     */
    function ytr($y_world)
    {
        if ($this->yscale_type == 'log') {
            //minus because GD defines y = 0 at top. doh!
            $y_pixels =  $this->plot_origin_y - log10($y_world) * $this->yscale ;
        } else {
            $y_pixels =  $this->plot_origin_y - $y_world * $this->yscale ;
        }
        return round($y_pixels);
    }

    /* A public interface to xtr and ytr. Translates (x,y) in world coordinates
     * to (x,y) in device coordinates and returns them as an array.
     * Usage is: list($x_pixel, $y_pixel) = $plot->GetDeviceXY($x_world, $y_world)
     */
    function GetDeviceXY($x_world, $y_world)
    {
        if (!isset($this->xscale)) {
            return $this->PrintError("GetDeviceXY() was called before translation factors were calculated");
        }
        return array($this->xtr($x_world), $this->ytr($y_world));
    }

    /*
     * Calculate tick parameters: Start, end, and delta values. This is used
     * by both DrawXTicks() and DrawYTicks().
     * This currently uses the same simplistic method previously used by
     * PHPlot (basically just range/10), but splitting this out into its
     * own function is the first step in replacing the method.
     * This is also used by CalcMaxTickSize() for CalcMargins().
     *
     *   $which : 'x' or 'y' : Which tick parameters to calculate
     *
     * Returns an array of 3 elements: tick_start, tick_end, tick_step
     */
    protected function CalcTicks($which)
    {
        if ($which == 'x') {
            $num_ticks = $this->num_x_ticks;
            $tick_inc = $this->x_tick_inc;
            $data_max = $this->plot_max_x;
            $data_min = $this->plot_min_x;
            $skip_lo = $this->skip_left_tick;
            $skip_hi = $this->skip_right_tick;
            $anchor = &$this->x_tick_anchor; // Use reference because this might not be set
        } elseif ($which == 'y') {
            $num_ticks = $this->num_y_ticks;
            $tick_inc = $this->y_tick_inc;
            $data_max = $this->plot_max_y;
            $data_min = $this->plot_min_y;
            $skip_lo = $this->skip_bottom_tick;
            $skip_hi = $this->skip_top_tick;
            $anchor = &$this->y_tick_anchor; // Use reference because this might not be set
        } else {
            return $this->PrintError("CalcTicks: Invalid usage ($which)");
        }

        if (!empty($tick_inc)) {
            $tick_step = $tick_inc;
        } elseif (!empty($num_ticks)) {
            $tick_step = ($data_max - $data_min) / $num_ticks;
        } else {
            $tick_step = ($data_max - $data_min) / 10;
        }

        // NOTE: When working with floats, because of approximations when adding $tick_step,
        // the value may not quite reach the end, or may exceed it very slightly.
        // So apply a "fudge" factor.
        $tick_start = (double)$data_min;
        $tick_end = (double)$data_max + ($data_max - $data_min) / 10000.0;

        // If a tick anchor was given, adjust the start of the range so the anchor falls
        // at an exact tick mark (or would, if it was within range).
        if (isset($anchor)) {
            $tick_start = $anchor - $tick_step * floor(($anchor - $tick_start) / $tick_step);
        }

        // Lastly, adjust for option to skip left/bottom or right/top tick marks:
        if ($skip_lo)
            $tick_start += $tick_step;
        if ($skip_hi)
            $tick_end -= $tick_step;

        return array($tick_start, $tick_end, $tick_step);
    }

    /*
     * Calculate the size of the biggest tick label. This is used by CalcMargins().
     * For 'x' ticks, it returns the height . For 'y' ticks, it returns the width.
     * This means height along Y, or width along X - not relative to the text angle.
     * That is what we need to calculate the needed margin space.
     * (Previous versions of PHPlot estimated this, using the maximum X or Y value,
     * or maybe the longest string. That doesn't work. -10 is longer than 9, etc.
     * So this gets the actual size of each label, slow as that may be.
     */
    protected function CalcMaxTickLabelSize($which)
    {
        list($tick_start, $tick_end, $tick_step) = $this->CalcTicks($which);

        if ($which == 'x') {
            $font = $this->fonts['x_label'];
            $angle = $this->x_label_angle;
        } elseif ($which == 'y') {
            $font = $this->fonts['y_label'];
            $angle = $this->y_label_angle;
        } else {
            return $this->PrintError("CalcMaxTickLabelSize: Invalid usage ($which)");
        }

        $max_width = 0;
        $max_height = 0;

        // Loop over ticks, same as DrawXTicks and DrawYTicks:
        // Avoid cumulative round-off errors from $val += $delta
        $n = 0;
        $tick_val = $tick_start;
        while ($tick_val <= $tick_end) {
            $tick_label = $this->FormatLabel($which, $tick_val);
            list($width, $height) = $this->SizeText($font, $angle, $tick_label);
            if ($width > $max_width) $max_width = $width;
            if ($height > $max_height) $max_height = $height;
            $tick_val = $tick_start + ++$n * $tick_step;
        }
        if ($this->GetCallback('debug_scale')) {
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'which' => $which, 'height' => $max_height, 'width' => $max_width));
        }

        if ($which == 'x')
            return $max_height;
        return $max_width;
    }

    /*
     * Calculate the size of the biggest data label. This is used by CalcMargins().
     * For $which='x', it returns the height of labels along the top or bottom.
     * For $which='y', it returns the width of labels along the left or right sides.
     * There is only one set of data labels (the first position in each data record).
     * They normally go along the top or bottom (or both). If the data type indicates
     * X/Y swapping (which is used for horizontal bar charts), the data labels go
     * along the sides instead. So CalcMaxDataLabelSize('x') returns 0 if the
     * data is X/Y swapped, and CalcMaxDataLabelSize('y') returns 0 if the data is
     * is not X/Y swapped.
     */
    protected function CalcMaxDataLabelSize($which = 'x')
    {
        if ($which == 'x') {
          if ($this->datatype_swapped_xy)
              return 0; // Shortcut: labels aren't on top/bottom.
          $font = $this->fonts['x_label'];
          $angle = $this->x_data_label_angle;
          $format_code = 'xd';
        } elseif ($which == 'y') {
          if (!$this->datatype_swapped_xy)
              return 0; // Shortcut: labels aren't on left/right.
          $font = $this->fonts['y_label'];
          $angle = $this->y_data_label_angle;
          $format_code = 'yd';
        } else {
          return $this->PrintError("CalcMaxDataLabelSize: Invalid usage ($which)");
        }
        $max_width = 0;
        $max_height = 0;

        // Loop over all data labels and find the biggest:
        for ($i = 0; $i < $this->num_data_rows; $i++) {
            $label = $this->FormatLabel($format_code, $this->data[$i][0]);
            list($width, $height) = $this->SizeText($font, $angle, $label);
            if ($width > $max_width) $max_width = $width;
            if ($height > $max_height) $max_height = $height;
        }
        if ($this->GetCallback('debug_scale')) {
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'height' => $max_height, 'width' => $max_width));
        }

        if ($this->datatype_swapped_xy)
            return $max_width;
        return $max_height;
    }

    /*
     * Set grid control defaults.
     * X grid defaults off, Y grid defaults on, except the reverse is true
     * with swapped graphs such as horizontal bars.
     */
    protected function CalcGridSettings()
    {
        if (!isset($this->draw_x_grid))
            $this->draw_x_grid = $this->datatype_swapped_xy;
        if (!isset($this->draw_y_grid))
            $this->draw_y_grid = !$this->datatype_swapped_xy;
    }

    /*
     * Helper for CheckLabels() - determine if there are any non-empty labels.
     * Returns True if all data labels are empty, else False.
     */
    protected function CheckLabelsAllEmpty()
    {
        for ($i = 0; $i < $this->num_data_rows; $i++)
            if ($this->data[$i][0] !== '') return FALSE;
        return TRUE;
    }

    /*
     * Check and set label parameters. This handles deferred processing for label
     * positioning and other label-related parameters.
     *   Copy label_format from 'x' to 'xd', and 'y' to 'yd', if not already set.
     *   Set x_data_label_angle from x_label_angle, if not already set.
     *   Apply defaults to X and Y tick and data label positions.
     * Note: the label strings in the data array are used as X data labels in
     * the normal case, but as Y data labels in the swapped X/Y case.
     */
    protected function CheckLabels()
    {
        // The X and Y data labels are formatted the same as X and Y tick labels,
        // unless overridden. Check and apply defaults for FormatLabel here:
        if (empty($this->label_format['xd']) && !empty($this->label_format['x']))
            $this->label_format['xd'] = $this->label_format['x'];
        if (empty($this->label_format['yd']) && !empty($this->label_format['y']))
            $this->label_format['yd'] = $this->label_format['y'];

        // The X tick label angle setting controls X data label angles too,
        // unless overridden. Check and apply the default here:
        if (!isset($this->x_data_label_angle))
            $this->x_data_label_angle = $this->x_label_angle;
        // Note: Y data label angle defaults to zero, unlike X,
        // for compatibility with older releases.

        // X Label position fixups, for x_data_label_pos and x_tick_label_pos:
        if ($this->datatype_swapped_xy) {
            // Just apply defaults - there is no position conflict for X labels.
            if (!isset($this->x_tick_label_pos))
                $this->x_tick_label_pos = 'plotdown';
            if (!isset($this->x_data_label_pos))
                $this->x_data_label_pos = 'none';
        } else {
            // Apply defaults but do not allow conflict between tick and data labels.
            if (isset($this->x_data_label_pos)) {
                if (!isset($this->x_tick_label_pos)) {
                    // Case: data_label_pos is set, tick_label_pos needs a default:
                    if ($this->x_data_label_pos == 'none')
                        $this->x_tick_label_pos = 'plotdown';
                    else
                        $this->x_tick_label_pos = 'none';
                }
            } elseif (isset($this->x_tick_label_pos)) {
                // Case: tick_label_pos is set, data_label_pos needs a default:
                if ($this->x_tick_label_pos == 'none')
                    $this->x_data_label_pos = 'plotdown';
                else
                    $this->x_data_label_pos = 'none';
            } else {
                // Case: Neither tick_label_pos nor data_label_pos is set.
                // We do not want them to be both on (as PHPlot used to do in this case).
                // Turn on data labels if any were supplied, else tick labels.
                if ($this->CheckLabelsAllEmpty()) {
                    $this->x_data_label_pos = 'none';
                    $this->x_tick_label_pos = 'plotdown';
                } else {
                    $this->x_data_label_pos = 'plotdown';
                    $this->x_tick_label_pos = 'none';
                }
            }
        }

        // Y Label position fixups, for y_data_label_pos and y_tick_label_pos:
        if (!$this->datatype_swapped_xy) {
            // Just apply defaults - there is no position conflict.
            if (!isset($this->y_tick_label_pos))
                $this->y_tick_label_pos = 'plotleft';
            if (!isset($this->y_data_label_pos))
                $this->y_data_label_pos = 'none';
        } else {
            // Apply defaults but do not allow conflict between tick and data labels.
            if (isset($this->y_data_label_pos)) {
                if (!isset($this->y_tick_label_pos)) {
                    // Case: data_label_pos is set, tick_label_pos needs a default:
                    if ($this->y_data_label_pos == 'none')
                        $this->y_tick_label_pos = 'plotleft';
                    else
                        $this->y_tick_label_pos = 'none';
                }
            } elseif (isset($this->y_tick_label_pos)) {
                // Case: tick_label_pos is set, data_label_pos needs a default:
                if ($this->y_tick_label_pos == 'none')
                    $this->y_data_label_pos = 'plotleft';
                else
                    $this->y_data_label_pos = 'none';
            } else {
                // Case: Neither tick_label_pos nor data_label_pos is set.
                // Turn on data labels if any were supplied, else tick labels.
                if ($this->CheckLabelsAllEmpty()) {
                    $this->y_data_label_pos = 'none';
                    $this->y_tick_label_pos = 'plotleft';
                } else {
                    $this->y_data_label_pos = 'plotleft';
                    $this->y_tick_label_pos = 'none';
                }
            }
        }
        return TRUE;
    }

    /*
     * Formats a tick or data label.
     *    which_pos - 'x', 'xd', 'y', or 'yd', selects formatting controls.
     *        x, y are for tick labels; xd, yd are for data labels.
     *    which_lab - String to format as a label.
     * Credits: Time formatting suggested by Marlin Viss
     *          Custom formatting suggested by zer0x333
     * Notes:
     *   Type 'title' is obsolete and retained for compatibility.
     *   Class variable 'data_units_text' is retained as a suffix for 'data' type formatting for
     *      backward compatibility. Since there was never a function/method to set it, there
     *      could be somebody out there who sets it directly in the object.
     */
    protected function FormatLabel($which_pos, $which_lab)
    {
        // Assign a reference shortcut to the label format controls.
        // Note CheckLabels() made sure the 'xd' and 'yd' arrays are set.
        $format =& $this->label_format[$which_pos];

        // Don't format empty strings (especially as time or numbers), or if no type was set.
        if ($which_lab !== '' && !empty($format['type'])) {
            switch ($format['type']) {
            case 'title':  // Note: This is obsolete
                $which_lab = @ $this->data[$which_lab][0];
                break;
            case 'data':
                $which_lab = $format['prefix']
                           . $this->number_format($which_lab, $format['precision'])
                           . $this->data_units_text  // Obsolete
                           . $format['suffix'];
                break;
            case 'time':
                $which_lab = strftime($format['time_format'], $which_lab);
                break;
            case 'printf':
                $which_lab = sprintf($format['printf_format'], $which_lab);
                break;
            case 'custom':
                $which_lab = call_user_func($format['custom_callback'], $which_lab, $format['custom_arg']);
                break;

            }
        }
        return $which_lab;
    }

/////////////////////////////////////////////
///////////////                         TICKS
/////////////////////////////////////////////

    /*
     * Set the step (interval) between X ticks.
     * Use either this or SetNumXTicks(), not both, to control the X tick marks.
     */
    function SetXTickIncrement($which_ti='')
    {
        $this->x_tick_inc = $which_ti;
        if (!empty($which_ti)) {
            $this->num_x_ticks = '';
        }
        return TRUE;
    }

    /*
     * Set the step (interval) between Y ticks.
     * Use either this or SetNumYTicks(), not both, to control the Y tick marks.
     */
    function SetYTickIncrement($which_ti='')
    {
        $this->y_tick_inc = $which_ti;
        if (!empty($which_ti)) {
            $this->num_y_ticks = '';
        }
        return TRUE;
    }

    /*
     * Set the number of X tick marks.
     * Use either this or SetXTickIncrement(), not both, to control the X tick marks.
     */
    function SetNumXTicks($which_nt='')
    {
        $this->num_x_ticks = $which_nt;
        if (!empty($which_nt)) {
            $this->x_tick_inc = '';
        }
        return TRUE;
    }

    /*
     * Set the number of Y tick marks.
     * Use either this or SetYTickIncrement(), not both, to control the Y tick marks.
     */
    function SetNumYTicks($which_nt='')
    {
        $this->num_y_ticks = $which_nt;
        if (!empty($which_nt)) {
            $this->y_tick_inc = '';  //either use num_y_ticks or y_tick_inc, not both
        }
        return TRUE;
    }

    /*
     * Set the position for the X tick marks.
     * These can be above the plot, below, both positions, at the X axis, or suppressed.
     */
    function SetXTickPos($which_tp)
    {
        $this->x_tick_pos = $this->CheckOption($which_tp, 'plotdown, plotup, both, xaxis, none',
                                               __FUNCTION__);
        return (boolean)$this->x_tick_pos;
    }

    /*
     * Set the position for the Y tick marks.
     * These can be left of the plot, right, both positions, at the Y axis, or suppressed.
     */
    function SetYTickPos($which_tp)
    {
        $this->y_tick_pos = $this->CheckOption($which_tp, 'plotleft, plotright, both, yaxis, none',
                                              __FUNCTION__);
        return (boolean)$this->y_tick_pos;
    }

    /*
     * Skip the top-most Y axis tick mark and label if $skip is true.
     */
    function SetSkipTopTick($skip)
    {
        $this->skip_top_tick = (bool)$skip;
        return TRUE;
    }

    /*
     * Skip the bottom-most Y axis tick mark and label if $skip is true.
     */
    function SetSkipBottomTick($skip)
    {
        $this->skip_bottom_tick = (bool)$skip;
        return TRUE;
    }

    /*
     * Skip the left-most X axis tick mark and label if $skip is true.
     */
    function SetSkipLeftTick($skip)
    {
        $this->skip_left_tick = (bool)$skip;
        return TRUE;
    }

    /*
     * Skip the right-most X axis tick mark and label if $skip is true.
     */
    function SetSkipRightTick($skip)
    {
        $this->skip_right_tick = (bool)$skip;
        return TRUE;
    }

    /*
     * Set the outer length of X tick marks to $which_xln pixels.
     * This is the part of the tick mark that sticks out from the plot area.
     */
    function SetXTickLength($which_xln)
    {
        $this->x_tick_length = $which_xln;
        return TRUE;
    }

    /*
     * Set the outer length of Y tick marks to $which_yln pixels.
     * This is the part of the tick mark that sticks out from the plot area.
     */
    function SetYTickLength($which_yln)
    {
        $this->y_tick_length = $which_yln;
        return TRUE;
    }

    /*
     * Set the crossing length of X tick marks to $which_xc pixels.
     * This is the part of the tick mark that sticks into the plot area.
     */
    function SetXTickCrossing($which_xc)
    {
        $this->x_tick_cross = $which_xc;
        return TRUE;
    }

    /*
     * Set the crossing length of Y tick marks to $which_yc pixels.
     * This is the part of the tick mark that sticks into the plot area.
     */
    function SetYTickCrossing($which_yc)
    {
        $this->y_tick_cross = $which_yc;
        return TRUE;
    }

    /*
     * Set an anchor point for X tick marks. There will be an X tick mark at
     * this exact value (if the data range were extended to include it).
     */
    function SetXTickAnchor($xta = NULL)
    {
        $this->x_tick_anchor = $xta;
        return TRUE;
    }

    /*
     * Set an anchor point for Y tick marks. There will be a Y tick mark at
     * this exact value (if the data range were extended to include it).
     */
    function SetYTickAnchor($yta = NULL)
    {
        $this->y_tick_anchor = $yta;
        return TRUE;
    }

/////////////////////////////////////////////
////////////////////          GENERIC DRAWING
/////////////////////////////////////////////

    /*
     * Fill the image background, with a tiled image file or solid color.
     */
    protected function DrawBackground()
    {
        // Don't draw this twice if drawing two plots on one image
        if (empty($this->done['background'])) {
            if (isset($this->bgimg)) {    // If bgimg is defined, use it
                $this->tile_img($this->bgimg, 0, 0, $this->image_width, $this->image_height, $this->bgmode);
            } else {                        // Else use solid color
                ImageFilledRectangle($this->img, 0, 0, $this->image_width, $this->image_height,
                                     $this->ndx_bg_color);
            }
            $this->done['background'] = TRUE;
        }
        return TRUE;
    }

    /*
     * Fill the plot area background, with a tiled image file or solid color.
     */
    protected function DrawPlotAreaBackground()
    {
        if (isset($this->plotbgimg)) {
            $this->tile_img($this->plotbgimg, $this->plot_area[0], $this->plot_area[1],
                            $this->plot_area_width, $this->plot_area_height, $this->plotbgmode);
        } elseif ($this->draw_plot_area_background) {
            ImageFilledRectangle($this->img, $this->plot_area[0], $this->plot_area[1],
                                 $this->plot_area[2], $this->plot_area[3], $this->ndx_plot_bg_color);
        }
        return TRUE;
    }

    /*
     * Tiles an image at some given coordinates.
     *   $file : Filename of the picture to be used as tile.
     *   $xorig : X device coordinate of where the tile is to begin.
     *   $yorig : Y device coordinate of where the tile is to begin.
     *   $width : Width of the area to be tiled.
     *   $height : Height of the area to be tiled.
     *   $mode : Tiling mode. One of 'centeredtile', 'tile', 'scale'.
     */
    protected function tile_img($file, $xorig, $yorig, $width, $height, $mode)
    {
        $im = $this->GetImage($file, $tile_width, $tile_height);
        if (!$im)
            return FALSE;  // GetImage already produced an error message.

        if ($mode == 'scale') {
            imagecopyresampled($this->img, $im, $xorig, $yorig, 0, 0, $width, $height,
                               $tile_width, $tile_height);
            return TRUE;
        }

        if ($mode == 'centeredtile') {
            $x0 = - floor($tile_width/2);   // Make the tile look better
            $y0 = - floor($tile_height/2);
        } else {      // Accept anything else as $mode == 'tile'
            $x0 = 0;
            $y0 = 0;
        }

        // Draw the tile onto a temporary image first.
        $tmp = imagecreate($width, $height);
        if (! $tmp)
            return $this->PrintError('tile_img(): Could not create image resource.');

        for ($x = $x0; $x < $width; $x += $tile_width)
            for ($y = $y0; $y < $height; $y += $tile_height)
                imagecopy($tmp, $im, $x, $y, 0, 0, $tile_width, $tile_height);

        // Copy the temporary image onto the final one.
        imagecopy($this->img, $tmp, $xorig, $yorig, 0,0, $width, $height);

        // Free resources
        imagedestroy($tmp);
        imagedestroy($im);

        return TRUE;
    }

    /*
     * Return the image border width.
     * This is used by CalcMargins() and DrawImageBorder().
     */
    protected function GetImageBorderWidth()
    {
        if ($this->image_border_type == 'none')
            return 0; // No border
        if (!empty($this->image_border_width))
            return $this->image_border_width; // Specified border width
        if ($this->image_border_type == 'raised')
            return 2; // Default for raised border is 2 pixels.
        return 1; // Default for other border types is 1 pixel.
    }

    /*
     * Draws a border around the final image.
     * Note: 'plain' draws a flat border using the dark shade of the border color.
     * This probably should have been written to use the actual border color, but
     * it is too late to fix it without changing plot appearances. Therefore a
     * new type 'solid' was added to use the SetImageBorderColor color.
     */
    protected function DrawImageBorder()
    {
        // Do nothing if already drawn, or if no border has been set.
        if ($this->image_border_type == 'none' || !empty($this->done['border']))
            return TRUE;
        $width = $this->GetImageBorderWidth();
        $color1 = $this->ndx_i_border;
        $color2 = $this->ndx_i_border_dark;
        $ex = $this->image_width - 1;
        $ey = $this->image_height - 1;
        switch ($this->image_border_type) {
        case 'raised':
            // Top and left lines use border color, right and bottom use the darker shade.
            // Drawing order matters in the upper right and lower left corners.
            for ($i = 0; $i < $width; $i++, $ex--, $ey--) {
                imageline($this->img, $i,  $i,  $ex, $i,  $color1); // Top
                imageline($this->img, $ex, $i,  $ex, $ey, $color2); // Right
                imageline($this->img, $i,  $i,  $i,  $ey, $color1); // Left
                imageline($this->img, $i,  $ey, $ex, $ey, $color2); // Bottom
            }
            break;
        case 'plain': // See note above re colors
            $color1 = $color2;
            // Fall through
        case 'solid':
            for ($i = 0; $i < $width; $i++, $ex--, $ey--) {
                imagerectangle($this->img, $i, $i, $ex, $ey, $color1);
            }
            break;
        default:
            return $this->PrintError(
                          "DrawImageBorder(): unknown image_border_type: '$this->image_border_type'");
        }
        $this->done['border'] = TRUE; // Border should only be drawn once per image.
        return TRUE;
    }

    /*
     * Draws the main title on the graph.
     * The title must not be drawn more than once (in the case of multiple plots
     * on the image), because TTF text antialiasing makes it look bad.
     */
    protected function DrawTitle()
    {
        if (!empty($this->done['title']) || $this->title_txt === '')
            return TRUE;

        // Center of the image:
        $xpos = $this->image_width / 2;

        // Place it at almost at the top
        $ypos = $this->title_offset;

        $this->DrawText($this->fonts['title'], 0, $xpos, $ypos,
                        $this->ndx_title_color, $this->title_txt, 'center', 'top');

        $this->done['title'] = TRUE;
        return TRUE;
    }

    /*
     * Draws the X-Axis Title
     */
    protected function DrawXTitle()
    {
        if ($this->x_title_pos == 'none')
            return TRUE;

        // Center of the plot
        $xpos = ($this->plot_area[2] + $this->plot_area[0]) / 2;

        // Upper title
        if ($this->x_title_pos == 'plotup' || $this->x_title_pos == 'both') {
            $ypos = $this->plot_area[1] - $this->x_title_top_offset;
            $this->DrawText($this->fonts['x_title'], 0, $xpos, $ypos, $this->ndx_x_title_color,
                            $this->x_title_txt, 'center', 'bottom');
        }
        // Lower title
        if ($this->x_title_pos == 'plotdown' || $this->x_title_pos == 'both') {
            $ypos = $this->plot_area[3] + $this->x_title_bot_offset;
            $this->DrawText($this->fonts['x_title'], 0, $xpos, $ypos, $this->ndx_x_title_color,
                            $this->x_title_txt, 'center', 'top');
        }
        return TRUE;
    }

    /*
     * Draws the Y-Axis Title
     */
    protected function DrawYTitle()
    {
        if ($this->y_title_pos == 'none')
            return TRUE;

        // Center the title vertically to the plot area
        $ypos = ($this->plot_area[3] + $this->plot_area[1]) / 2;

        if ($this->y_title_pos == 'plotleft' || $this->y_title_pos == 'both') {
            $xpos = $this->plot_area[0] - $this->y_title_left_offset;
            $this->DrawText($this->fonts['y_title'], 90, $xpos, $ypos, $this->ndx_y_title_color,
                            $this->y_title_txt, 'right', 'center');
        }
        if ($this->y_title_pos == 'plotright' || $this->y_title_pos == 'both') {
            $xpos = $this->plot_area[2] + $this->y_title_right_offset;
            $this->DrawText($this->fonts['y_title'], 90, $xpos, $ypos, $this->ndx_y_title_color,
                            $this->y_title_txt, 'left', 'center');
        }

        return TRUE;
    }

    /*
     * Draw the X axis, including ticks and labels, and X (vertical) grid lines.
     */
    protected function DrawXAxis()
    {
        // Draw ticks, labels and grid
        $this->DrawXTicks();

        //Draw X Axis at Y = x_axis_y_pixels, unless suppressed (See SetXAxisPosition)
        if (empty($this->suppress_x_axis)) {
            ImageLine($this->img, $this->plot_area[0]+1, $this->x_axis_y_pixels,
                      $this->plot_area[2]-1, $this->x_axis_y_pixels, $this->ndx_grid_color);
        }
        return TRUE;
    }

    /*
     * Draw the Y axis, including ticks and labels, and Y (horizontal) grid lines.
     * Horizontal grid lines overwrite horizontal axis with y=0, so call this first, then DrawXAxis()
     */
    protected function DrawYAxis()
    {
        // Draw ticks, labels and grid
        $this->DrawYTicks();

        // Draw Y axis at X = y_axis_x_pixels, unless suppressed (See SetYAxisPosition)
        if (empty($this->suppress_y_axis)) {
            ImageLine($this->img, $this->y_axis_x_pixels, $this->plot_area[1],
                      $this->y_axis_x_pixels, $this->plot_area[3], $this->ndx_grid_color);
        }
        return TRUE;
    }

    /*
     * Draw one X tick mark and its tick label.
     *   $which_xlab : Formatted X value for the label.
     *   $which_xpix : X device coordinate for this tick mark.
     */
    protected function DrawXTick($which_xlab, $which_xpix)
    {
        // Ticks on X axis
        if ($this->x_tick_pos == 'xaxis') {
            ImageLine($this->img, $which_xpix, $this->x_axis_y_pixels - $this->x_tick_cross,
                      $which_xpix, $this->x_axis_y_pixels + $this->x_tick_length, $this->ndx_tick_color);
        }

        // Ticks on top of the Plot Area
        if ($this->x_tick_pos == 'plotup' || $this->x_tick_pos == 'both') {
            ImageLine($this->img, $which_xpix, $this->plot_area[1] - $this->x_tick_length,
                      $which_xpix, $this->plot_area[1] + $this->x_tick_cross, $this->ndx_tick_color);
        }

        // Ticks on bottom of Plot Area
        if ($this->x_tick_pos == 'plotdown' || $this->x_tick_pos == 'both') {
            ImageLine($this->img, $which_xpix, $this->plot_area[3] + $this->x_tick_length,
                      $which_xpix, $this->plot_area[3] - $this->x_tick_cross, $this->ndx_tick_color);
        }

        // Label on X axis
        if ($this->x_tick_label_pos == 'xaxis') {
            $this->DrawText($this->fonts['x_label'], $this->x_label_angle,
                            $which_xpix, $this->x_axis_y_pixels + $this->x_label_axis_offset,
                            $this->ndx_text_color, $which_xlab, 'center', 'top');
        }

        // Label on top of the Plot Area
        if ($this->x_tick_label_pos == 'plotup' || $this->x_tick_label_pos == 'both') {
            $this->DrawText($this->fonts['x_label'], $this->x_label_angle,
                            $which_xpix, $this->plot_area[1] - $this->x_label_top_offset,
                            $this->ndx_text_color, $which_xlab, 'center', 'bottom');
        }

        // Label on bottom of the Plot Area
        if ($this->x_tick_label_pos == 'plotdown' || $this->x_tick_label_pos == 'both') {
            $this->DrawText($this->fonts['x_label'], $this->x_label_angle,
                            $which_xpix, $this->plot_area[3] + $this->x_label_bot_offset,
                            $this->ndx_text_color, $which_xlab, 'center', 'top');
        }
        return TRUE;
    }

    /*
     * Draw one Y tick mark and its tick label. Called from DrawYTicks() and DrawXAxis()
     *   $which_ylab : Formatted Y value for the label.
     *   $which_ypix : Y device coordinate for this tick mark.
     */
    protected function DrawYTick($which_ylab, $which_ypix)
    {
        // Ticks on Y axis
        if ($this->y_tick_pos == 'yaxis') {
            ImageLine($this->img, $this->y_axis_x_pixels - $this->y_tick_length, $which_ypix,
                      $this->y_axis_x_pixels + $this->y_tick_cross, $which_ypix, $this->ndx_tick_color);
        }

        // Ticks to the left of the Plot Area
        if (($this->y_tick_pos == 'plotleft') || ($this->y_tick_pos == 'both') ) {
            ImageLine($this->img, $this->plot_area[0] - $this->y_tick_length, $which_ypix,
                      $this->plot_area[0] + $this->y_tick_cross, $which_ypix, $this->ndx_tick_color);
        }

        // Ticks to the right of the Plot Area
        if (($this->y_tick_pos == 'plotright') || ($this->y_tick_pos == 'both') ) {
            ImageLine($this->img, $this->plot_area[2] + $this->y_tick_length, $which_ypix,
                      $this->plot_area[2] - $this->y_tick_cross, $which_ypix, $this->ndx_tick_color);
        }

        // Labels on Y axis
        if ($this->y_tick_label_pos == 'yaxis') {
            $this->DrawText($this->fonts['y_label'], $this->y_label_angle,
                            $this->y_axis_x_pixels - $this->y_label_axis_offset, $which_ypix,
                            $this->ndx_text_color, $which_ylab, 'right', 'center');
        }

        // Labels to the left of the plot area
        if ($this->y_tick_label_pos == 'plotleft' || $this->y_tick_label_pos == 'both') {
            $this->DrawText($this->fonts['y_label'], $this->y_label_angle,
                            $this->plot_area[0] - $this->y_label_left_offset, $which_ypix,
                            $this->ndx_text_color, $which_ylab, 'right', 'center');
        }
        // Labels to the right of the plot area
        if ($this->y_tick_label_pos == 'plotright' || $this->y_tick_label_pos == 'both') {
            $this->DrawText($this->fonts['y_label'], $this->y_label_angle,
                            $this->plot_area[2] + $this->y_label_right_offset, $which_ypix,
                            $this->ndx_text_color, $which_ylab, 'left', 'center');
        }
        return TRUE;
    }

    /*
     * Draws Grid, Ticks and Tick Labels along X-Axis
     * Ticks and tick labels can be down of plot only, up of plot only,
     * both on up and down of plot, or crossing a user defined X-axis
     *
     * Original vertical code submitted by Marlin Viss
     */
    protected function DrawXTicks()
    {
        // Sets the line style for IMG_COLOR_STYLED lines (grid)
        if ($this->dashed_grid) {
            $this->SetDashedStyle($this->ndx_light_grid_color);
            $style = IMG_COLOR_STYLED;
        } else {
            $style = $this->ndx_light_grid_color;
        }

        // Calculate the tick start, end, and step:
        list($x_start, $x_end, $delta_x) = $this->CalcTicks('x');

        // Loop, avoiding cumulative round-off errors from $x_tmp += $delta_x
        $n = 0;
        $x_tmp = $x_start;
        while ($x_tmp <= $x_end) {
            $xlab = $this->FormatLabel('x', $x_tmp);
            $x_pixels = $this->xtr($x_tmp);

            // Vertical grid lines
            if ($this->draw_x_grid) {
                ImageLine($this->img, $x_pixels, $this->plot_area[1], $x_pixels, $this->plot_area[3], $style);
            }

            // Draw tick mark(s)
            $this->DrawXTick($xlab, $x_pixels);

            // Step to next X, without accumulating error
            $x_tmp = $x_start + ++$n * $delta_x;
        }
        return TRUE;
    }

    /*
     * Draw the grid, ticks, and tick labels along the Y axis.
     * Ticks and tick labels can be left of plot only, right of plot only,
     * both on the left and right of plot, or crossing a user defined Y-axis
     */
    protected function DrawYTicks()
    {
        // Sets the line style for IMG_COLOR_STYLED lines (grid)
        if ($this->dashed_grid) {
            $this->SetDashedStyle($this->ndx_light_grid_color);
            $style = IMG_COLOR_STYLED;
        } else {
            $style = $this->ndx_light_grid_color;
        }

        // Calculate the tick start, end, and step:
        list($y_start, $y_end, $delta_y) = $this->CalcTicks('y');

        // Loop, avoiding cumulative round-off errors from $y_tmp += $delta_y
        $n = 0;
        $y_tmp = $y_start;
        while ($y_tmp <= $y_end) {
            $ylab = $this->FormatLabel('y', $y_tmp);
            $y_pixels = $this->ytr($y_tmp);

            // Horizontal grid line
            if ($this->draw_y_grid) {
                ImageLine($this->img, $this->plot_area[0]+1, $y_pixels, $this->plot_area[2]-1,
                          $y_pixels, $style);
            }

            // Draw tick mark(s)
            $this->DrawYTick($ylab, $y_pixels);

            // Step to next Y, without accumulating error
            $y_tmp = $y_start + ++$n * $delta_y;
        }
        return TRUE;
    }

    /*
     *  Draw a border around the plot area. See SetPlotBorderType.
     *  Note: SetPlotBorderType sets plot_border_type to an array, but
     *  it won't be an array if it defaults or is set directly (backward compatibility).
     */
    protected function DrawPlotBorder()
    {
        $pbt = (array)$this->plot_border_type;
        $sides = 0;  // Bitmap: 1=left 2=top 4=right 8=bottom
        $map = array('left' => 1, 'plotleft' => 1, 'right' => 4, 'plotright' => 4, 'top' => 2,
                      'bottom' => 8, 'both' => 5, 'sides' => 5, 'full' => 15, 'none' => 0);
        foreach ($pbt as $option) $sides |= $map[$option];
        if ($sides == 15) { // Border on all 4 sides
            imagerectangle($this->img, $this->plot_area[0], $this->plot_area[1],
                           $this->plot_area[2], $this->plot_area[3], $this->ndx_grid_color);
        } else {
            if ($sides & 1) // Left
                imageline($this->img, $this->plot_area[0], $this->plot_area[1],
                                      $this->plot_area[0], $this->plot_area[3], $this->ndx_grid_color);
            if ($sides & 2) // Top
                imageline($this->img, $this->plot_area[0], $this->plot_area[1],
                                      $this->plot_area[2], $this->plot_area[1], $this->ndx_grid_color);
            if ($sides & 4) // Right
                imageline($this->img, $this->plot_area[2], $this->plot_area[1],
                                      $this->plot_area[2], $this->plot_area[3], $this->ndx_grid_color);
            if ($sides & 8) // Bottom
                imageline($this->img, $this->plot_area[0], $this->plot_area[3],
                                      $this->plot_area[2], $this->plot_area[3], $this->ndx_grid_color);
        }
        return TRUE;
    }

    /*
     * Draw the data value label associated with a point in the plot.
     * These are labels that show the value (dependent variable, usually Y) of the data point,
     * and are drawn within the plot area (not to be confused with axis data labels).
     *
     *    $x_or_y : Specify 'x' or 'y' labels. This selects font, angle, and formatting.
     *    $x_world, $y_world : World coordinates of the text (see also x/y_adjustment).
     *    $text : The text to draw, after formatting with FormatLabel().
     *    $halign, $valign : Selects from 9-point text alignment.
     *    $x_adjustment, $y_adjustment : Text position offsets, in device coordinates.
     *    $min_width, $min_height : If supplied, suppress the text if it will not fit.
     * Returns True, if the text was drawn, or False, if it will not fit.
     */
    protected function DrawDataValueLabel($x_or_y, $x_world, $y_world, $text, $halign, $valign,
                      $x_adjustment=0, $y_adjustment=0, $min_width=NULL, $min_height=NULL)
    {
        if ($x_or_y == 'x') {
            $angle = $this->x_data_label_angle;
            $font = $this->fonts['x_label'];
            $formatted_text = $this->FormatLabel('xd', $text);
        } else { // Assumed 'y'
            $angle = $this->y_data_label_angle;
            $font = $this->fonts['y_label'];
            $formatted_text = $this->FormatLabel('yd', $text);
        }
        $color = $this->ndx_title_color; // Currently this is the same for X and Y labels

        // Check to see if the text fits in the available space, if requested.
        if (isset($min_width) || isset($min_height)) {
            list($width, $height) = $this->SizeText($font, $angle, $formatted_text);
            if ((isset($min_width) && ($min_width - $width)  < 2)
                || (isset($min_height) && ($min_height - $height) < 2))
                return FALSE;
        }

        $this->DrawText($font, $angle, $this->xtr($x_world) + $x_adjustment,
                        $this->ytr($y_world) + $y_adjustment,
                        $color, $formatted_text, $halign, $valign);
        return TRUE;
    }

    /*
     * Draws the axis data label associated with a point in the plot.
     * This is different from x_labels drawn by DrawXTicks() and care
     * should be taken not to draw both, as they'd probably overlap.
     * Calling of this function in DrawLines(), etc is decided after x_data_label_pos value.
     * Leave the last parameter out, to avoid the drawing of vertical lines, no matter
     * what the setting is (for plots that need it, like DrawSquared())
     */
    protected function DrawXDataLabel($xlab, $xpos, $row=FALSE)
    {
        $xlab = $this->FormatLabel('xd', $xlab);

        // Labels below the plot area
        if ($this->x_data_label_pos == 'plotdown' || $this->x_data_label_pos == 'both')
            $this->DrawText($this->fonts['x_label'], $this->x_data_label_angle,
                            $xpos, $this->plot_area[3] + $this->x_label_bot_offset,
                            $this->ndx_text_color, $xlab, 'center', 'top');

        // Labels above the plot area
        if ($this->x_data_label_pos == 'plotup' || $this->x_data_label_pos == 'both')
            $this->DrawText($this->fonts['x_label'], $this->x_data_label_angle,
                            $xpos, $this->plot_area[1] - $this->x_label_top_offset,
                            $this->ndx_text_color, $xlab, 'center', 'bottom');

        // $row=0 means this is the first row. $row=FALSE means don't do any rows.
        if ($row !== FALSE && $this->draw_x_data_label_lines)
            $this->DrawXDataLine($xpos, $row);
        return TRUE;
    }

    /*
     * Draw a data label along the Y axis or side.
     * This is used by horizontal plots.
     */
    protected function DrawYDataLabel($ylab, $ypos)
    {
        $ylab = $this->FormatLabel('yd', $ylab);

        // Labels left of the plot area
        if ($this->y_data_label_pos == 'plotleft' || $this->y_data_label_pos == 'both')
            $this->DrawText($this->fonts['y_label'], $this->y_data_label_angle,
                            $this->plot_area[0] - $this->y_label_left_offset, $ypos,
                            $this->ndx_text_color, $ylab, 'right', 'center');

        // Labels right of the plot area
        if ($this->y_data_label_pos == 'plotright' || $this->y_data_label_pos == 'both')
            $this->DrawText($this->fonts['y_label'], $this->y_data_label_angle,
                            $this->plot_area[2] + $this->y_label_right_offset, $ypos,
                            $this->ndx_text_color, $ylab, 'left', 'center');
        return TRUE;
    }

    /*
     * Draws Vertical lines from data points up and down.
     * Which lines are drawn depends on the value of x_data_label_pos,
     * and whether this is at all done or not, on draw_x_data_label_lines
     *
     *   $xpos : position in pixels of the line.
     *   $row : index of the data row being drawn.
     */
    protected function DrawXDataLine($xpos, $row)
    {
        // Sets the line style for IMG_COLOR_STYLED lines (grid)
        if ($this->dashed_grid) {
            $this->SetDashedStyle($this->ndx_light_grid_color);
            $style = IMG_COLOR_STYLED;
        } else {
            $style = $this->ndx_light_grid_color;
        }

        if ($this->x_data_label_pos == 'both') {
            // Lines from the bottom up
            ImageLine($this->img, $xpos, $this->plot_area[3], $xpos, $this->plot_area[1], $style);
        } elseif ($this->x_data_label_pos == 'plotdown' && isset($this->data_max[$row])) {
            // Lines from the bottom of the plot up to the max Y value at this X:
            $ypos = $this->ytr($this->data_max[$row]);
            ImageLine($this->img, $xpos, $ypos, $xpos, $this->plot_area[3], $style);
        } elseif ($this->x_data_label_pos == 'plotup' && isset($this->data_min[$row])) {
            // Lines from the top of the plot down to the min Y value at this X:
            $ypos = $this->ytr($this->data_min[$row]);
            ImageLine($this->img, $xpos, $this->plot_area[1], $xpos, $ypos, $style);
        }
        return TRUE;
    }

/////////////////////////////////////////////
///////////////                        LEGEND
/////////////////////////////////////////////

    /*
     * Set text to display in the graph's legend.
     *   $which_leg : Array of strings for the complete legend, or a single string
     *                to be appended to the legend.
     *                Or NULL (or an empty array) to cancel the legend.
     */
    function SetLegend($which_leg)
    {
        if (is_array($which_leg)) {           // use array (or cancel, if empty array)
            $this->legend = $which_leg;
        } elseif (!is_null($which_leg)) {     // append string
            $this->legend[] = $which_leg;
        } else {
            $this->legend = '';  // Reinitialize to empty, meaning no legend.
        }
        return TRUE;
    }

    /*
     * Specifies the position of the legend's upper/leftmost corner, in pixel (device) coordinates.
     * Both X and Y must be provided, or both omitted (or use NULL) to restore auto-positioning.
     */
    function SetLegendPixels($which_x=NULL, $which_y=NULL)
    {
        return $this->SetLegendPosition(0, 0, 'image', 0, 0, $which_x, $which_y);
    }

    /*
     * Specifies the position of the legend's upper/leftmost corner, in world (data space) coordinates.
     */
    function SetLegendWorld($which_x, $which_y)
    {
        return $this->SetLegendPosition(0, 0, 'world', $which_x, $which_y);
    }

    /*
     * Specifies the position of the legend. This includes SetLegendWorld(), SetLegendPixels(), and
     * additional choices using relative coordinates, with optional pixel offset.
     *   $x, $y : Relative coordinates of a point on the legend box. (See below)
     *   $relative_to : What to position the legend relative to: 'image', 'plot', 'world', or 'title'.
     *   $x_base, $y_base : Base point for positioning.
     *      If $relative_to is 'world', then this is a world coordinate position.
     *      Otherwise, this is a relative coordinate position on the $relative_to element.
     *   $x_offset, $y_offset : Additional legend box offset in device coordinates (pixels).
     *  The legend is positioned so that point ($x,$y) is at ($x_base, $y_base).
     *  'Relative coordinates' means: (0,0) is the upper left corner, and (1,1) is the lower right corner
     *  of the element (legend, image, plot, or title area), regardless of its size. These are floating
     *  point values, each usually in the range [0,1], but they can be negative or greater than 1.
     *  If any of x, y, x_offset, or y_offset are NULL, default legend positioning is restored.
     */
    function SetLegendPosition($x, $y, $relative_to, $x_base, $y_base, $x_offset = 0, $y_offset = 0)
    {
        // Special case: NULL means restore the default positioning.
        if (!isset($x, $y, $x_offset, $y_offset)) {
            unset($this->legend_pos);
        } else {
            $mode = $this->CheckOption($relative_to, 'image, plot, title, world', __FUNCTION__);
            if (empty($mode))
                return FALSE;
            // Save all values for use by GetLegendPosition()
            $this->legend_pos = compact('x', 'y', 'mode', 'x_base', 'y_base', 'x_offset', 'y_offset');
        }
        return TRUE;
    }

    /*
     * Set legend text alignment, color box alignment, and style options.
     *   $text_align : Alignment of the text, 'left' or 'right'.
     *   $colorbox_align : Alignment of the color boxes, 'left', 'right', 'none', or missing/empty.
     *       If missing or empty, the same alignment as $text_align is used. Color box is positioned first.
     */
    function SetLegendStyle($text_align, $colorbox_align = '')
    {
        $this->legend_text_align = $this->CheckOption($text_align, 'left, right', __FUNCTION__);
        if (empty($colorbox_align))
            $this->legend_colorbox_align = $this->legend_text_align;
        else
            $this->legend_colorbox_align = $this->CheckOption($colorbox_align, 'left, right, none',
                                                              __FUNCTION__);
        return ((boolean)$this->legend_text_align && (boolean)$this->legend_colorbox_align);
    }

    /*
     * Use color boxes or point shapes (for points and linepoints plots only) in the legend.
     *   $use_shapes : True to use point shapes, false to use color boxes.
     */
    function SetLegendUseShapes($use_shapes)
    {
        $this->legend_use_shapes = (bool)$use_shapes;
        return TRUE;
    }

    /*
     * Reverse the order of legend lines. This is useful with stackedbars and stackedarea
     * plots, so the legend entries are ordered the same way as the plot sections.
     */
    function SetLegendReverse($reversal = False)
    {
        $this->legend_reverse_order = (bool)$reversal;
        return TRUE;
    }

    /*
     * Get legend sizing parameters.
     * This is used internally by DrawLegend(), and also by the public GetLegendSize().
     * It returns information based on any SetLegend*() calls already made. It does not use
     * legend position or data scaling, so it can be called before data scaling is set up.
     * Returns an associative array with these entries describing legend sizing:
     *    'width', 'height' : Overall legend box size in pixels.
     *    'char_w', 'char_h' : Width and height of 'E' in legend text font. (Used to size color boxes)
     *    'v_margin' : Inside margin for legend
     *    'text_align', 'colorbox_align' : Same as the class variables, with default applied.
     *    'draw_colorbox' : True if color boxes will be drawn.
     *    'dot_height' : Height of color boxes (even if not drawn), for line spacing.
     *    'colorbox_width' : Width of color boxes.
     */
    protected function GetLegendSizeParams()
    {
        $font = &$this->fonts['legend']; // Shortcut to font info array

        // Find maximum legend label line width.
        $max_width = 0;
        foreach ($this->legend as $line) {
            list($width, $unused) = $this->SizeText($font, 0, $line);
            if ($width > $max_width) $max_width = $width;
        }

        // Font parameters are used to size the color boxes:
        $char_w = $font['width'];
        $char_h = $font['height'];
        $line_spacing = $this->GetLineSpacing($font);

        // Apply defaults to text alignment and colorbox alignment variables:
        $text_align = isset($this->legend_text_align) ? $this->legend_text_align : 'right';
        $colorbox_align = isset($this->legend_colorbox_align) ? $this->legend_colorbox_align : 'right';
        $draw_colorbox = ($colorbox_align != 'none');

        // Sizing parameters:
        $v_margin = $char_h / 2;                 // Between vertical borders and labels
        $dot_height = $char_h + $line_spacing;   // Height of the color boxes (even if not drawn)
        $colorbox_width = $char_w;               // Base color box width
        if (isset($this->legend_colorbox_width))
            $colorbox_width *= $this->legend_colorbox_width; // Adjustment to color box width

        // Calculate overall legend box width and height.
        // Width is e.g.: "| space colorbox space text space |" where each space adds $char_w,
        // and colorbox (if drawn) adds $char_w * its width adjustment.
        if ($draw_colorbox) {
            $width = $max_width + 3 * $char_w + $colorbox_width;
        } else {
            $width = $max_width + 2 * $char_w;
        }
        $height = $dot_height * count($this->legend) + 2 * $v_margin;

        return compact('width', 'height', 'char_w', 'char_h', 'v_margin',
              'text_align', 'colorbox_align', 'draw_colorbox', 'dot_height', 'colorbox_width');
    }

    /*
     * Get legend box size. This can be used to adjust the plot margins, for example.
     * Returns: Array of ($width, $height) of the legend box in pixels.
     */
    function GetLegendSize()
    {
        $params = $this->GetLegendSizeParams();
        return array($params['width'], $params['height']);
    }

    /*
     * Get legend location in device coordinates. This is a helper for DrawLegend, and is only
     * called if there is a legend. See SetLegendWorld(), SetLegendPixels(), SetLegendPosition().
     *   $width, $height : Width and height of the legend box.
     * Returns: coordinates of the upper left corner of the legend box as an array ($x, $y)
     */
    protected function GetLegendPosition($width, $height)
    {
        // Extract variables set by SetLegend*(): $mode, $x, $y, $x_base, $y_base, $x_offset, $y_offset
        if (isset($this->legend_pos['mode']))
            extract($this->legend_pos);
        else
            $mode = ''; // Default legend position mode.

        switch ($mode) {

        case 'plot': // SetLegendPosition with mode='plot', relative coordinates over plot area.
            return array((int)($x_base * $this->plot_area_width - $x * $width)
                          + $this->plot_area[0] + $x_offset,
                         (int)($y_base * $this->plot_area_height - $y * $height)
                          + $this->plot_area[1] + $y_offset);

        case 'world': // User-defined position in world-coordinates (SetLegendWorld), using x_base, y_base
            return array($this->xtr($x_base) + $x_offset - (int)($x * $width),
                         $this->ytr($y_base) + $y_offset - (int)($y * $height));

        case 'image': // SetLegendPosition with mode='image', relative coordinates over image area.
                      // SetLegendPixels() uses this too, with x=y=0.
            return array((int)($x_base * $this->image_width - $x * $width) + $x_offset,
                         (int)($y_base * $this->image_height - $y * $height) + $y_offset);

        case 'title': // SetLegendPosition with mode='title', relative to main title.
            // Recalculate main title position/size, since CalcMargins does not save it. See DrawTitle()
            list($title_width, $title_height) = $this->SizeText($this->fonts['title'], 0, $this->title_txt);
            $title_x = (int)(($this->image_width - $title_width) / 2);
            return array((int)($x_base * $title_width - $x * $width) + $title_x + $x_offset,
                         (int)($y_base * $title_height - $y * $height) + $this->title_offset + $y_offset);

        default: // If mode is unset (or invalid), use default position.
            return array ($this->plot_area[2] - $width - $this->safe_margin,
                          $this->plot_area[1] + $this->safe_margin);
        }
    }

    /*
     * Draws the graph legend
     * This is called by DrawGraph only if $this->legend is not empty.
     * Base code submitted by Marlin Viss
     */
    protected function DrawLegend()
    {
        $font = &$this->fonts['legend']; // Shortcut to font info array

        // Calculate legend box sizing parameters:
        // See GetLegendSizeParams() to see what variables are set by this.
        extract($this->GetLegendSizeParams());

        // Get legend box position:
        list($box_start_x, $box_start_y) = $this->GetLegendPosition($width, $height);
        $box_end_y = $box_start_y + $height;
        $box_end_x = $box_start_x + $width;

        // Draw outer box
        ImageFilledRectangle($this->img, $box_start_x, $box_start_y, $box_end_x, $box_end_y,
                             $this->ndx_bg_color);
        ImageRectangle($this->img, $box_start_x, $box_start_y, $box_end_x, $box_end_y,
                       $this->ndx_grid_color);

        $color_index = 0;
        $max_color_index = count($this->ndx_data_colors) - 1;

        // Calculate color box and text horizontal positions.
        if (!$draw_colorbox) {
            if ($text_align == 'left')
                $x_pos = $box_start_x + $char_w;
            else
                $x_pos = $box_end_x - $char_w;
            $dot_left_x = 0; // Not used directly if color boxes/shapes are off, but referenced below.
        } elseif ($colorbox_align == 'left') {
            $dot_left_x = $box_start_x + $char_w;
            $dot_right_x = $dot_left_x + $colorbox_width;
            if ($text_align == 'left')
                $x_pos = $dot_right_x + $char_w;
            else
                $x_pos = $box_end_x - $char_w;
        } else {      // $colorbox_align == 'right'
            $dot_right_x = $box_end_x - $char_w;
            $dot_left_x = $dot_right_x - $colorbox_width;
            if ($text_align == 'left')
                $x_pos = $box_start_x + $char_w;
            else
                $x_pos = $dot_left_x - $char_w;
        }

        // $y_pos is the bottom of each color box. $yc is the vertical center of the color box or
        // the point shape (if drawn). The text is centered vertically on $yc.
        // For normal order (top-down), $y_pos starts at the top. For reversed order, at the bottom.
        if (empty($this->legend_reverse_order)) {
            $y_pos = $box_start_y + $v_margin + $dot_height;
            $delta_y = $dot_height;
        } else {
            $y_pos = $box_end_y - $v_margin;
            $delta_y = -$dot_height;
        }
        $yc = (int)($y_pos - $dot_height / 2);
        $xc = (int)($dot_left_x + $colorbox_width / 2);   // Horizontal center for point shape if drawn
        $shape_index = 0;  // Shape number index, if drawing point shapes

        // Option to use point shapes rather than solid boxes. Disallow this if the shapes array
        // has not been initialized (see CheckPointParams). Only works with 'points' or 'linepoints' plots.
        $use_shapes = !empty($this->legend_use_shapes) && !empty($this->point_counts);

        foreach ($this->legend as $leg) {
            // Draw text with requested alignment:
            $this->DrawText($font, 0, $x_pos, $yc, $this->ndx_text_color, $leg, $text_align, 'center');
            if ($draw_colorbox) {
                $y1 = $y_pos - $dot_height + 1;
                $y2 = $y_pos - 1;
                if ($use_shapes) {
                    // Draw a point shape in the data color
                    // If plot area background is on, use that as the shape background:
                    if ($this->draw_plot_area_background) {
                        ImageFilledRectangle($this->img, $dot_left_x, $y1, $dot_right_x, $y2,
                                             $this->ndx_plot_bg_color);
                    }
                    // Draw the shape. DrawShape() takes shape_index modulo number of defined shapes.
                    $this->DrawShape($xc, $yc, $shape_index++, $this->ndx_data_colors[$color_index]);
                } else {
                    // Draw color boxes:
                    ImageFilledRectangle($this->img, $dot_left_x, $y1, $dot_right_x, $y2,
                                         $this->ndx_data_colors[$color_index]);
                   // Draw a rectangle around the box
                   ImageRectangle($this->img, $dot_left_x, $y1, $dot_right_x, $y2, $this->ndx_text_color);
                }
            }
            $y_pos += $delta_y;
            $yc += $delta_y;
            if (++$color_index > $max_color_index)
                $color_index = 0;
        }
        return TRUE;
    }

/////////////////////////////////////////////
////////////////////     PLOT DRAWING HELPERS
/////////////////////////////////////////////

    /*
     * Get data color to use for plotting.
     *   $row, $idx : Index arguments for the current data point.
     *   &$vars : Variable storage. Caller makes an empty array, and this function uses it.
     *   &$data_color : Returned result - Color index for the data point.
     *   $extra : Extra info flag passed through to data color callback.
     */
    protected function GetDataColor($row, $idx, &$vars, &$data_color, $extra = 0)
    {
        // Initialize or extract variables:
        if (empty($vars)) {
            $custom_color = (bool)$this->GetCallback('data_color');
            $num_data_colors = count($this->ndx_data_colors);
            $vars = compact('custom_color', 'num_data_colors');
        } else {
            extract($vars);
        }

        // Select the colors.
        if ($custom_color) {
            $col_i = $this->DoCallback('data_color', $row, $idx, $extra); // Custom color index
            $data_color = $this->ndx_data_colors[$col_i % $num_data_colors];
        } else {
            $data_color = $this->ndx_data_colors[$idx];
        }
    }

    /*
     * Get data color and error bar color to use for plotting.
     *   $row, $idx : Index arguments for the current bar.
     *   &$vars : Variable storage. Caller makes an empty array, and this function uses it.
     *   &$data_color : Returned result - Color index for the data (bar fill)
     *   &$error_color : Returned result - Color index for the error bars
     *   $extra : Extra info flag passed through to data color callback.
     */
    protected function GetDataErrorColors($row, $idx, &$vars, &$data_color, &$error_color, $extra = 0)
    {
        // Initialize or extract variables:
        if (empty($vars)) {
            $this->NeedErrorBarColors();   // This plot needs error bar colors.
            $custom_color = (bool)$this->GetCallback('data_color');
            $num_data_colors = count($this->ndx_data_colors);
            $num_error_colors = count($this->ndx_error_bar_colors);
            $vars = compact('custom_color', 'num_data_colors', 'num_error_colors');
        } else {
            extract($vars);
        }

        // Select the colors.
        if ($custom_color) {
            $col_i = $this->DoCallback('data_color', $row, $idx, $extra); // Custom color index
            $data_color = $this->ndx_data_colors[$col_i % $num_data_colors];
            $error_color = $this->ndx_error_bar_colors[$col_i % $num_error_colors];
        } else {
            $data_color = $this->ndx_data_colors[$idx];
            $error_color = $this->ndx_error_bar_colors[$idx];
        }
    }

    /*
     * Get colors to use for a bar chart. There is a data color, and either a border color
     * or a shading color (data dark color).
     *   $row, $idx : Index arguments for the current bar.
     *   &$vars : Variable storage. Caller makes an empty array, and this function uses it.
     *   &$data_color : Returned result - Color index for the data (bar fill).
     *   &$alt_color : Returned result - Color index for the shading or outline.
     */
    protected function GetBarColors($row, $idx, &$vars, &$data_color, &$alt_color)
    {
        // Initialize or extract variables:
        if (empty($vars)) {
            if ($this->shading > 0)    // This plot needs dark colors if shading is on.
                $this->NeedDataDarkColors();
            $custom_color = (bool)$this->GetCallback('data_color');
            $num_data_colors = count($this->ndx_data_colors);
            $num_border_colors = count($this->ndx_data_border_colors);
            $vars = compact('custom_color', 'num_data_colors', 'num_border_colors');
        } else {
            extract($vars);
        }

        // Select the colors.
        if ($custom_color) {
            $col_i = $this->DoCallback('data_color', $row, $idx); // Custom color index
            $i_data = $col_i % $num_data_colors; // Index for data colors and dark colors
            $i_border = $col_i % $num_border_colors; // Index for data borders (if used)
        } else {
            $i_data = $i_border = $idx;
        }
        $data_color = $this->ndx_data_colors[$i_data];
        if ($this->shading > 0) {
            $alt_color = $this->ndx_data_dark_colors[$i_data];
        } else {
            $alt_color = $this->ndx_data_border_colors[$i_border];
        }
    }

    /*
     * Draw a shape (dot, point). This is the bottom half of DrawDot, and is also
     * used by legend drawing. Unlike DrawDot this takes device coordinates.
     * The list of supported shapes can also be found in SetPointShapes().
     *   $x, $y - Device coordinates of the center of the shape
     *   $record - Index into point_shapes[] and point_sizes[]. This is taken modulo the array sizes.
     *   $color - Shape color to use.
     */
    protected function DrawShape($x, $y, $record, $color)
    {
        $index = $record % $this->point_counts;
        $point_size = $this->point_sizes[$index];
        $half_point = (int)($point_size / 2);

        $x1 = $x - $half_point;
        $x2 = $x + $half_point;
        $y1 = $y - $half_point;
        $y2 = $y + $half_point;

        switch ($this->point_shapes[$index]) {
        case 'halfline':
            ImageLine($this->img, $x1, $y, $x, $y, $color);
            break;
        case 'line':
            ImageLine($this->img, $x1, $y, $x2, $y, $color);
            break;
        case 'plus':
            ImageLine($this->img, $x1, $y, $x2, $y, $color);
            ImageLine($this->img, $x, $y1, $x, $y2, $color);
            break;
        case 'cross':
            ImageLine($this->img, $x1, $y1, $x2, $y2, $color);
            ImageLine($this->img, $x1, $y2, $x2, $y1, $color);
            break;
        case 'circle':
            ImageArc($this->img, $x, $y, $point_size, $point_size, 0, 360, $color);
            break;
        case 'dot':
            ImageFilledEllipse($this->img, $x, $y, $point_size, $point_size, $color);
            break;
        case 'diamond':
            $arrpoints = array($x1, $y, $x, $y1, $x2, $y, $x, $y2);
            ImageFilledPolygon($this->img, $arrpoints, 4, $color);
            break;
        case 'triangle':
            $arrpoints = array($x1, $y, $x2, $y, $x, $y2);
            ImageFilledPolygon($this->img, $arrpoints, 3, $color);
            break;
        case 'trianglemid':
            $arrpoints = array($x1, $y1, $x2, $y1, $x, $y);
            ImageFilledPolygon($this->img, $arrpoints, 3, $color);
            break;
        case 'yield':
            $arrpoints = array($x1, $y1, $x2, $y1, $x, $y2);
            ImageFilledPolygon($this->img, $arrpoints, 3, $color);
            break;
        case 'delta':
            $arrpoints = array($x1, $y2, $x2, $y2, $x, $y1);
            ImageFilledPolygon($this->img, $arrpoints, 3, $color);
            break;
        case 'star':
            ImageLine($this->img, $x1, $y, $x2, $y, $color);
            ImageLine($this->img, $x, $y1, $x, $y2, $color);
            ImageLine($this->img, $x1, $y1, $x2, $y2, $color);
            ImageLine($this->img, $x1, $y2, $x2, $y1, $color);
            break;
        case 'hourglass':
            $arrpoints = array($x1, $y1, $x2, $y1, $x1, $y2, $x2, $y2);
            ImageFilledPolygon($this->img, $arrpoints, 4, $color);
            break;
        case 'bowtie':
            $arrpoints = array($x1, $y1, $x1, $y2, $x2, $y1, $x2, $y2);
            ImageFilledPolygon($this->img, $arrpoints, 4, $color);
            break;
        case 'target':
            ImageFilledRectangle($this->img, $x1, $y1, $x, $y, $color);
            ImageFilledRectangle($this->img, $x, $y, $x2, $y2, $color);
            ImageRectangle($this->img, $x1, $y1, $x2, $y2, $color);
            break;
        case 'box':
            ImageRectangle($this->img, $x1, $y1, $x2, $y2, $color);
            break;
        case 'home': /* As in: "home plate" (baseball), also looks sort of like a house. */
            $arrpoints = array($x1, $y2, $x2, $y2, $x2, $y, $x, $y1, $x1, $y);
            ImageFilledPolygon($this->img, $arrpoints, 5, $color);
            break;
        case 'up':
            ImagePolygon($this->img, array($x, $y1, $x2, $y2, $x1, $y2), 3, $color);
            break;
        case 'down':
            ImagePolygon($this->img, array($x, $y2, $x1, $y1, $x2, $y1), 3, $color);
            break;
        case 'none': /* Special case, no point shape here */
            break;
        default: /* Also 'rect' */
            ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $color);
            break;
        }
        return TRUE;
    }

    /*
     * Draws a styled dot. Uses world coordinates.
     * Note: DrawShape() does all the work.
     */
    protected function DrawDot($x_world, $y_world, $record, $color)
    {
        return $this->DrawShape($this->xtr($x_world), $this->ytr($y_world), $record, $color);
    }

    /*
     * Draw a bar (or segment of a bar), with optional shading or border.
     * This is used by the bar and stackedbar plots, vertical and horizontal.
     *   $x1, $y1 : One corner of the bar.
     *   $x2, $y2 : Other corner of the bar.
     *   $data_color : Color index to use for the bar fill.
     *   $alt_color : Color index to use for the shading (if shading is on), else for the border.
     *      Note the same color is NOT used for shading and border - just the same argument.
     *      See GetBarColors() for where these arguments come from.
     *   $shade_top : Shade the top? (Suppressed for downward stack segments except first.)
     *   $shade_side : Shade the right side? (Suppressed for leftward stack segments except first.)
     *      Only one of $shade_top or $shade_side can be FALSE. Both default to TRUE.
     */
    protected function DrawBar($x1, $y1, $x2, $y2, $data_color, $alt_color,
            $shade_top = TRUE, $shade_side = TRUE)
    {
        // Sort the points so x1,y1 is upper left and x2,y2 is lower right. This
        // is needed in order to get the shading right, and imagerectangle may require it.
        if ($x1 > $x2) {
            $t = $x1; $x1 = $x2; $x2 = $t;
        }
        if ($y1 > $y2) {
            $t = $y1; $y1 = $y2; $y2 = $t;
        }

        // Draw the bar
        ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $data_color);

        // Draw a shade, or a border.
        if (($shade = $this->shading) > 0) {
            if ($shade_top && $shade_side) {
                $npts = 6;
                $pts = array($x1, $y1, $x1 + $shade, $y1 - $shade, $x2 + $shade, $y1 - $shade,
                             $x2 + $shade, $y2 - $shade, $x2, $y2, $x2, $y1);
            } else {
                $npts = 4;
                if ($shade_top) { // Suppress side shading
                    $pts = array($x1, $y1, $x1 + $shade, $y1 - $shade, $x2 + $shade, $y1 - $shade, $x2, $y1);
                } else { // Suppress top shading
                    $pts = array($x2, $y2, $x2, $y1, $x2 + $shade, $y1 - $shade, $x2 + $shade, $y2 - $shade);
                }
            }
            ImageFilledPolygon($this->img, $pts, $npts, $alt_color);
        } else {
            ImageRectangle($this->img, $x1, $y1, $x2,$y2, $alt_color);
        }
    }

    /*
     *  Draw an Error Bar set. Used by DrawDotsError and DrawLinesError
     */
    protected function DrawYErrorBar($x_world, $y_world, $error_height, $error_bar_type, $color)
    {
        $x1 = $this->xtr($x_world);
        $y1 = $this->ytr($y_world);
        $y2 = $this->ytr($y_world+$error_height) ;

        ImageSetThickness($this->img, $this->error_bar_line_width);
        ImageLine($this->img, $x1, $y1 , $x1, $y2, $color);
        if ($error_bar_type == 'tee') {
            ImageLine($this->img, $x1-$this->error_bar_size, $y2, $x1+$this->error_bar_size, $y2, $color);
        }
        ImageSetThickness($this->img, 1);
        return TRUE;
    }

/////////////////////////////////////////////
////////////////////             PLOT DRAWING
/////////////////////////////////////////////

    /*
     * Draws a pie chart. Data is 'text-data', 'data-data', or 'text-data-single'.
     *
     *  For text-data-single, the data array contains records with an ignored label,
     *  and one Y value. Each record defines a sector of the pie, as a portion of
     *  the sum of all Y values.
     *
     *  For text-data and data-data, the data array contains records with an ignored label,
     *  an ignored X value (for data-data only), and N (N>=1) Y values per record.
     *  The pie chart will be produced with N segments. The relative size of the first
     *  sector of the pie is the sum of the first Y data value in each record, etc.
     *
     *  Note: With text-data-single, the data labels could be used, but are not currently.
     *
     *  If there are no valid data points > 0 at all, just draw nothing. It may seem more correct to
     *  raise an error, but all of the other plot types handle it this way implicitly. DrawGraph
     *  checks for an empty data array, but this is different: a non-empty data array with no Y values,
     *  or all Y=0.
     */
    protected function DrawPieChart()
    {
        if (!$this->CheckDataType('text-data, text-data-single, data-data'))
            return FALSE;

        // Allocate dark colors only if they will be used for shading.
        if ($this->shading > 0)
            $this->NeedDataDarkColors();

        $xpos = $this->plot_area[0] + $this->plot_area_width/2;
        $ypos = $this->plot_area[1] + $this->plot_area_height/2;
        $diameter = min($this->plot_area_width, $this->plot_area_height);
        $radius = $diameter/2;

        $num_slices = $this->data_columns;  // See CheckDataArray which calculates this for us.
        if ($num_slices < 1) return TRUE;   // Give up early if there is no data at all.
        $sumarr = array_fill(0, $num_slices, 0);

        if ($this->datatype_pie_single) {
            // text-data-single: One data column per row, one pie slice per row.
            for ($i = 0; $i < $num_slices; $i++) {
                // $legend[$i] = $this->data[$i][0];                // Note: Labels are not used yet
                if (is_numeric($this->data[$i][1]))
                    $sumarr[$i] = abs($this->data[$i][1]);
            }
        } else {
            // text-data: Sum each column (skipping label), one pie slice per column.
            // data-data: Sum each column (skipping X value and label), one pie slice per column.
            $skip = ($this->datatype_implied) ? 1 : 2; // Leading values to skip in each row.
            for ($i = 0; $i < $this->num_data_rows; $i++) {
                for ($j = $skip; $j < $this->num_recs[$i]; $j++) {
                    if (is_numeric($this->data[$i][$j]))
                        $sumarr[$j-$skip] += abs($this->data[$i][$j]);
                }
            }
        }

        $total = array_sum($sumarr);

        if ($total == 0) {
            // There are either no valid data points, or all are 0.
            // See top comment about why not to make this an error.
            return TRUE;
        }

        if ($this->shading) {
            $diam2 = $diameter / 2;
        } else {
            $diam2 = $diameter;
        }
        $max_data_colors = count($this->ndx_data_colors);

        // Use the Y label format precision, with default value:
        if (isset($this->label_format['y']['precision']))
            $precision = $this->label_format['y']['precision'];
        else
            $precision = 1;

        for ($h = $this->shading; $h >= 0; $h--) {
            $color_index = 0;
            $start_angle = 0;
            $end_angle = 0;
            for ($j = 0; $j < $num_slices; $j++) {
                $val = $sumarr[$j];

                // For shaded pies: the last one (at the top of the "stack") has a brighter color:
                if ($h == 0)
                    $slicecol = $this->ndx_data_colors[$color_index];
                else
                    $slicecol = $this->ndx_data_dark_colors[$color_index];

                $label_txt = $this->number_format(($val / $total * 100), $precision) . '%';
                $val = 360 * ($val / $total);

                // NOTE that imagefilledarc measures angles CLOCKWISE (go figure why),
                // so the pie chart would start clockwise from 3 o'clock, would it not be
                // for the reversal of start and end angles in imagefilledarc()
                // Also note ImageFilledArc only takes angles in integer degrees, and if the
                // the start and end angles match then you get a full circle not a zero-width
                // pie. This is bad. So skip any zero-size wedge. On the other hand, we cannot
                // let cumulative error from rounding to integer result in missing wedges. So
                // keep the running total as a float, and round the angles. It should not
                // be necessary to check that the last wedge ends at 360 degrees.
                $start_angle = $end_angle;
                $end_angle += $val;
                // This method of conversion to integer - truncate after reversing it - was
                // chosen to match the implicit method of PHPlot<=5.0.4 to get the same slices.
                $arc_start_angle = (int)(360 - $start_angle);
                $arc_end_angle = (int)(360 - $end_angle);

                if ($arc_start_angle > $arc_end_angle) {
                    $mid_angle = deg2rad($end_angle - ($val / 2));

                    // Draw the slice
                    ImageFilledArc($this->img, $xpos, $ypos+$h, $diameter, $diam2,
                                   $arc_end_angle, $arc_start_angle,
                                   $slicecol, IMG_ARC_PIE);

                    // Draw the labels only once
                    if ($h == 0) {
                        // Draw the outline
                        if (! $this->shading)
                            ImageFilledArc($this->img, $xpos, $ypos+$h, $diameter, $diam2,
                                           $arc_end_angle, $arc_start_angle, $this->ndx_grid_color,
                                           IMG_ARC_PIE | IMG_ARC_EDGED |IMG_ARC_NOFILL);

                        // The '* 1.2' trick is to get labels out of the pie chart so there are more
                        // chances they can be seen in small sectors.
                        $label_x = $xpos + ($diameter * 1.2 * cos($mid_angle)) * $this->label_scale_position;
                        $label_y = $ypos+$h - ($diam2 * 1.2 * sin($mid_angle)) * $this->label_scale_position;

                        $this->DrawText($this->fonts['generic'], 0, $label_x, $label_y, $this->ndx_grid_color,
                                        $label_txt, 'center', 'center');
                    }
                }
                if (++$color_index >= $max_data_colors)
                    $color_index = 0;
            }   // end for
        }   // end for
        return TRUE;
    }

    /*
     * Draw the points and errors bars for an error plot of types points and linepoints
     * Supports only data-data-error format, with each row of the form
     *   array("title", x, y1, error1+, error1-, y2, error2+, error2-, ...)
     * This is called from DrawDots, with data type already checked.
     *   $paired is true for linepoints error plots, to make sure elements are
     *       only drawn once.  If true, data labels are drawn by DrawLinesError, and error
     *       bars are drawn by DrawDotsError. (This choice is for backwards compatibility.)
     */
    protected function DrawDotsError($paired = FALSE)
    {
        // Adjust the point shapes and point sizes arrays:
        $this->CheckPointParams();

        $gcvars = array(); // For GetDataErrorColors, which initializes and uses this.
        // Special flag for data color callback to indicate the 'points' part of 'linepoints':
        $alt_flag = $paired ? 1 : 0;

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                // Skip record #0 (title)

            $x_now = $this->data[$row][$record++];  // Read it, advance record index

            $x_now_pixels = $this->xtr($x_now);             // Absolute coordinates.

            // Draw X Data labels?
            if ($this->x_data_label_pos != 'none' && !$paired)
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels, $row);

            // Now go for Y, E+, E-
            for ($idx = 0; $record < $this->num_recs[$row]; $idx++) {
                if (is_numeric($this->data[$row][$record])) {         // Allow for missing Y data

                    // Select the colors:
                    $this->GetDataErrorColors($row, $idx, $gcvars, $data_color, $error_color, $alt_flag);

                    // Y:
                    $y_now = $this->data[$row][$record++];
                    $this->DrawDot($x_now, $y_now, $idx, $data_color);

                    // Error +
                    $val = $this->data[$row][$record++];
                    $this->DrawYErrorBar($x_now, $y_now, $val, $this->error_bar_shape, $error_color);
                    // Error -
                    $val = $this->data[$row][$record++];
                    $this->DrawYErrorBar($x_now, $y_now, -$val, $this->error_bar_shape, $error_color);
                } else {
                    $record += 3;  // Skip over missing Y and its error values
                }
            }
        }
        return TRUE;
    }

    /*
     * Draw a points plot, or the points for a linepoints plot
     * Data format can be text-data (label, y1, y2, ...) or data-data (label, x, y1, y2, ...)
     * Points plot with error bars (data-data-error format) is redirected to DrawDotsError.
     *   $paired is true for linepoints plots, to make sure elements are only drawn once.
     */
    protected function DrawDots($paired = FALSE)
    {
        if (!$this->CheckDataType('text-data, data-data, data-data-error'))
            return FALSE;
        if ($this->datatype_error_bars)
            return $this->DrawDotsError($paired); // Redirect for points+errorbars plot

        // Adjust the point shapes and point sizes arrays:
        $this->CheckPointParams();

        $gcvars = array(); // For GetDataColor, which initializes and uses this.
        // Special flag for data color callback to indicate the 'points' part of 'linepoints':
        $alt_flag = $paired ? 1 : 0;

        // Data Value Labels? (Skip if doing the points from a linepoints plot)
        $do_dvls = !$paired && $this->CheckDataValueLabels($this->y_data_label_pos,
                      $dvl_x_off, $dvl_y_off, $dvl_h_align, $dvl_v_align);

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $rec = 1;                    // Skip record #0 (data label)

            if ($this->datatype_implied)                    // Implied X values?
                $x_now = 0.5 + $row;                        // Place text-data at X = 0.5, 1.5, 2.5, etc...
            else
                $x_now = $this->data[$row][$rec++];         // Read it, advance record index

            $x_now_pixels = $this->xtr($x_now);

            // Draw X Data labels?
            if (!$paired && $this->x_data_label_pos != 'none')
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels, $row);

            // Proceed with Y values
            for ($idx = 0;$rec < $this->num_recs[$row]; $rec++, $idx++) {
                if (is_numeric($this->data[$row][$rec])) {              // Allow for missing Y data
                    $y_now = (double)$this->data[$row][$rec];

                    // Select the color:
                    $this->GetDataColor($row, $idx, $gcvars, $data_color, $alt_flag);
                    // Draw the marker:
                    $this->DrawDot($x_now, $y_now, $idx, $data_color);

                    // Draw data value labels?
                    if ($do_dvls) {
                        $this->DrawDataValueLabel('y', $x_now, $y_now, $y_now, $dvl_h_align, $dvl_v_align,
                                                  $dvl_x_off, $dvl_y_off);
                    }
                }
            }
        }
        return TRUE;
    }

    /*
     * Draw a Thin Bar Line plot, also known as an Impulse plot.
     * A clean, fast routine for when you just want charts like stock volume charts.
     * Supports data-data and text-data formats for vertical plots,
     * and data-data-yx and text-data-yx for horizontal plots.
     * Note that although this plot type supports multiple data sets, it rarely makes
     * sense to have more than 1, because the lines will overlay.
     * This one function does both vertical and horizontal plots. "iv" is used for the
     * independent variable (X for vertical plots, Y for horizontal) and "dv" is used
     * for the dependent variable (Y for vertical plots, X for horizontal).
     */
    protected function DrawThinBarLines()
    {
        if (!$this->CheckDataType('text-data, data-data, text-data-yx, data-data-yx'))
            return FALSE;

        $gcvars = array(); // For GetDataColor, which initializes and uses this.

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $rec = 1;                    // Skip record #0 (data label)

            if ($this->datatype_implied)                    // Implied independent variable values?
                $iv_now = 0.5 + $row;                       // Place text-data at 0.5, 1.5, 2.5, etc...
            else
                $iv_now = $this->data[$row][$rec++];        // Read it, advance record index

            if ($this->datatype_swapped_xy) {
                $y_now_pixels = $this->ytr($iv_now);
                // Draw Y Data labels?
                if ($this->y_data_label_pos != 'none')
                    $this->DrawYDataLabel($this->data[$row][0], $y_now_pixels);
            } else {
                $x_now_pixels = $this->xtr($iv_now);
                // Draw X Data labels?
                if ($this->x_data_label_pos != 'none')
                    $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels);
            }

            // Proceed with dependent values
            for ($idx = 0; $rec < $this->num_recs[$row]; $rec++, $idx++) {
                if (is_numeric($this->data[$row][$rec])) {              // Allow for missing data
                    $dv = $this->data[$row][$rec];
                    ImageSetThickness($this->img, $this->line_widths[$idx]);

                    // Select the color:
                    $this->GetDataColor($row, $idx, $gcvars, $data_color);

                    if ($this->datatype_swapped_xy) {
                        // Draw a line from user defined y axis position right (or left) to xtr($dv)
                        ImageLine($this->img, $this->y_axis_x_pixels, $y_now_pixels,
                                              $this->xtr($dv), $y_now_pixels, $data_color);
                    } else {
                        // Draw a line from user defined x axis position up (or down) to ytr($dv)
                        ImageLine($this->img, $x_now_pixels, $this->x_axis_y_pixels,
                                              $x_now_pixels, $this->ytr($dv), $data_color);
                   }
                }
            }
        }

        ImageSetThickness($this->img, 1);
        return TRUE;
    }

    /*
     * Draw an 'area' or 'stacked area' plot.
     * Both of these fill the area between lines, but in the stacked area graph the Y values
     * are accumulated for each X, same as stacked bars. In the regular area graph, the areas
     * are filled in order from the X axis up to each Y (so the Y values for each X need to be
     * in decreasing order in this case).
     * Data format can be text-data (label, y1, y2, ...) or data-data (label, x, y1, y2, ...)
     * Notes:
     *   All Y values must be >= 0. (If any Y<0 the absolute value is used.)
     *   Missing data points are NOT handled. (They are counted as 0.)
     *   All rows must have the same number of Y points, or an error image will be produced.
     */
    protected function DrawArea($do_stacked = FALSE)
    {
        if (!$this->CheckDataType('text-data, data-data'))
            return FALSE;

        $n = $this->num_data_rows;  // Number of X values
        if ($n < 2) return TRUE;    // Require at least 2 rows, for imagefilledpolygon().

        // These arrays store the device X and Y coordinates for all lines:
        $xd = array();
        $yd = array();

        // Make sure each row has the same number of values. Note records_per_group is max(num_recs).
        if ($this->records_per_group != min($this->num_recs)) {
            return $this->PrintError("DrawArea(): Data array must contain the same number"
                      . " of Y values for each X");
        }

        // Calculate the Y value for each X, and store the device
        // coordinates into the xd and yd arrays.
        // For stacked area plots, the Y values accumulate.
        for ($row = 0; $row < $n; $row++) {
            $rec = 1;                                       // Skip record #0 (data label)

            if ($this->datatype_implied)                    // Implied X values?
                $x_now = 0.5 + $row;                        // Place text-data at X = 0.5, 1.5, 2.5, etc...
            else
                $x_now = $this->data[$row][$rec++];         // Read it, advance record index

            $x_now_pixels = $this->xtr($x_now);

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels);

            // Store the X value.
            // There is an artificial Y value at the axis. For 'area' it goes
            // at the end; for stackedarea it goes before the start.
            $xd[$row] = $x_now_pixels;
            $yd[$row] = array();
            if ($do_stacked)
                $yd[$row][] = $this->x_axis_y_pixels;

            // Store the Y values for this X.
            // All Y values are clipped to the x axis which should be zero but can be moved.
            $y = 0;
            while ($rec < $this->records_per_group) {
                if (is_numeric($this->data[$row][$rec])) {  // Treat missing values as 0.
                    $y += abs($this->data[$row][$rec]);
                }
                $yd[$row][] = $this->ytr(max($this->x_axis_position, $y));
                if (!$do_stacked) $y = 0;
                $rec++;
            }

            if (!$do_stacked)
                $yd[$row][] = $this->x_axis_y_pixels;
        }

        // Now draw the filled polygons.
        // Note data_columns is the number of Y points (columns excluding label and X), and the
        // number of entries in the yd[] arrays is data_columns+1.
        $prev_row = 0;
        for ($row = 1; $row <= $this->data_columns; $row++) { // 1 extra for X axis artificial row
            $pts = array();
            // Previous data set forms top (for area) or bottom (for stackedarea):
            for ($j = 0; $j < $n; $j++) {
                $pts[] = $xd[$j];
                $pts[] = $yd[$j][$prev_row];
            }
            // Current data set forms bottom (for area) or top (for stackedarea):
            for ($j = $n- 1; $j >= 0; $j--) {
                $pts[] = $xd[$j];
                $pts[] = $yd[$j][$row];
            }
            // Draw it:
            ImageFilledPolygon($this->img, $pts, $n * 2, $this->ndx_data_colors[$prev_row]);

            $prev_row = $row;
        }
        return TRUE;
    }

    /*
     * Draw a line plot, or the lines part of a linepoints plot
     * Data format can be text-data (label, y1, y2, ...) or data-data (label, x, y1, y2, ...)
     * Line plot with error bars (data-data-error format) is redirected to DrawLinesError.
     *   $paired is true for linepoints plots, to make sure elements are only drawn once.
     */
    protected function DrawLines($paired = FALSE)
    {
        if (!$this->CheckDataType('text-data, data-data, data-data-error'))
            return FALSE;
        if ($this->datatype_error_bars)
            return $this->DrawLinesError($paired); // Redirect for lines+errorbar plot

        // Flag array telling if the current point is valid, one element per plot line.
        // If start_lines[i] is true, then (lastx[i], lasty[i]) is the previous point.
        if ($this->data_columns == 0) return TRUE; // No data to plot; prevent array_fill error.
        $start_lines = array_fill(0, $this->data_columns, FALSE);

        $gcvars = array(); // For GetDataColor, which initializes and uses this.

        // Data Value Labels?
        $do_dvls = $this->CheckDataValueLabels($this->y_data_label_pos,
                      $dvl_x_off, $dvl_y_off, $dvl_h_align, $dvl_v_align);

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            if ($this->datatype_implied)                    // Implied X values?
                $x_now = 0.5 + $row;                        // Place text-data at X = 0.5, 1.5, 2.5, etc...
            else
                $x_now = $this->data[$row][$record++];      // Read it, advance record index

            $x_now_pixels = $this->xtr($x_now);             // Absolute coordinates

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels, $row);

            for ($idx = 0; $record < $this->num_recs[$row]; $record++, $idx++) {
                if (($line_style = $this->line_styles[$idx]) == 'none')
                    continue; //Allow suppressing entire line, useful with linepoints
                if (is_numeric($this->data[$row][$record])) {           //Allow for missing Y data
                    $y_now = (double)$this->data[$row][$record];
                    $y_now_pixels = $this->ytr($y_now);

                    if ($start_lines[$idx]) {
                        // Set line width, revert it to normal at the end
                        ImageSetThickness($this->img, $this->line_widths[$idx]);

                        // Select the color:
                        $this->GetDataColor($row, $idx, $gcvars, $data_color);

                        if ($line_style == 'dashed') {
                            $this->SetDashedStyle($data_color);
                            $data_color = IMG_COLOR_STYLED;
                        }
                        ImageLine($this->img, $x_now_pixels, $y_now_pixels,
                                  $lastx[$idx], $lasty[$idx], $data_color);
                    }

                    // Draw data value labels?
                    if ($do_dvls) {
                        $this->DrawDataValueLabel('y', $x_now, $y_now, $y_now, $dvl_h_align, $dvl_v_align,
                                                  $dvl_x_off, $dvl_y_off);
                    }

                    $lasty[$idx] = $y_now_pixels;
                    $lastx[$idx] = $x_now_pixels;
                    $start_lines[$idx] = TRUE;
                } elseif ($this->draw_broken_lines) {  // Y data missing, leave a gap.
                    $start_lines[$idx] = FALSE;
                }
            }   // end for
        }   // end for

        ImageSetThickness($this->img, 1);       // Revert to original state for lines to be drawn later.
        return TRUE;
    }

    /*
     * Draw lines with error bars for an error plot of types lines and linepoints
     * Supports only data-data-error format, with each row of the form
     *   array("title", x, y1, error1+, error1-, y2, error2+, error2-, ...)
     * This is called from DrawLines, with data type already checked.
     *   $paired is true for linepoints error plots, to make sure elements are
     *       only drawn once.  If true, data labels are drawn by DrawLinesError, and error
     *       bars are drawn by DrawDotsError. (This choice is for backwards compatibility.)
     */
    protected function DrawLinesError($paired = FALSE)
    {
        if ($this->data_columns == 0) return TRUE; // No data to plot; prevent array_fill error.
        $start_lines = array_fill(0, $this->data_columns, FALSE);

        $gcvars = array(); // For GetDataErrorColors, which initializes and uses this.

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            $x_now = $this->data[$row][$record++];          // Read X value, advance record index

            $x_now_pixels = $this->xtr($x_now);             // Absolute coordinates.

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels, $row);

            // Now go for Y, E+, E-
            for ($idx = 0; $record < $this->num_recs[$row]; $idx++) {
                if (($line_style = $this->line_styles[$idx]) == 'none')
                    continue; //Allow suppressing entire line, useful with linepoints
                if (is_numeric($this->data[$row][$record])) {    // Allow for missing Y data

                    // Select the colors:
                    $this->GetDataErrorColors($row, $idx, $gcvars, $data_color, $error_color);

                    // Y
                    $y_now = $this->data[$row][$record++];
                    $y_now_pixels = $this->ytr($y_now);

                    if ($start_lines[$idx]) {
                        ImageSetThickness($this->img, $this->line_widths[$idx]);

                        if ($line_style == 'dashed') {
                            $this->SetDashedStyle($data_color);
                            $data_color = IMG_COLOR_STYLED;
                        }
                        ImageLine($this->img, $x_now_pixels, $y_now_pixels,
                                  $lastx[$idx], $lasty[$idx], $data_color);
                    }

                    if ($paired) {
                        $record += 2; // Skip error bars - done in the 'points' part of 'linepoints'.
                    } else {
                        // Error+
                        $val = $this->data[$row][$record++];
                        $this->DrawYErrorBar($x_now, $y_now, $val, $this->error_bar_shape, $error_color);

                        // Error-
                        $val = $this->data[$row][$record++];
                        $this->DrawYErrorBar($x_now, $y_now, -$val, $this->error_bar_shape, $error_color);
                    }

                    // Update indexes:
                    $start_lines[$idx] = TRUE;   // Tells us if we already drew the first column of points,
                                             // thus having $lastx and $lasty ready for the next column.
                    $lastx[$idx] = $x_now_pixels;
                    $lasty[$idx] = $y_now_pixels;

                } else {
                    $record += 3;  // Skip over missing Y and its error values
                    if ($this->draw_broken_lines) {
                        $start_lines[$idx] = FALSE;
                    }
                }
            }   // end for
        }   // end for

        ImageSetThickness($this->img, 1);   // Revert to original state for lines to be drawn later.
        return TRUE;
    }

    /*
     * Draw a Lines+Points plot (linepoints).
     * This just uses DrawLines and DrawDots. They handle the error-bar case themselves.
     */
    protected function DrawLinePoints()
    {
        // This check is redundant, as DrawLines and DrawDots do it, but left here as insurance.
        if (!$this->CheckDataType('text-data, data-data, data-data-error'))
            return FALSE;
        $this->DrawLines(TRUE);
        $this->DrawDots(TRUE);
        return TRUE;
    }

    /*
     * Draw a Squared Line plot.
     * Data format can be text-data (label, y1, y2, ...) or data-data (label, x, y1, y2, ...)
     * This is based on DrawLines(), with one more line drawn for each point.
     */
    protected function DrawSquared()
    {
        if (!$this->CheckDataType('text-data, data-data'))
            return FALSE;

        if ($this->data_columns == 0) return TRUE; // No data to plot; prevent array_fill error.
        // Flag array telling if the current point is valid, one element per plot line.
        // If start_lines[i] is true, then (lastx[i], lasty[i]) is the previous point.
        $start_lines = array_fill(0, $this->data_columns, FALSE);

        $gcvars = array(); // For GetDataColor, which initializes and uses this.

        // Data Value Labels?
        $do_dvls = $this->CheckDataValueLabels($this->y_data_label_pos,
                      $dvl_x_off, $dvl_y_off, $dvl_h_align, $dvl_v_align);

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            if ($this->datatype_implied)                    // Implied X values?
                $x_now = 0.5 + $row;                        // Place text-data at X = 0.5, 1.5, 2.5, etc...
            else
                $x_now = $this->data[$row][$record++];      // Read it, advance record index

            $x_now_pixels = $this->xtr($x_now);             // Absolute coordinates

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels); // notice there is no last param.

            // Draw Lines
            for ($idx = 0; $record < $this->num_recs[$row]; $record++, $idx++) {
                if (is_numeric($this->data[$row][$record])) {               // Allow for missing Y data
                    $y_now = (double)$this->data[$row][$record];
                    $y_now_pixels = $this->ytr($y_now);

                    if ($start_lines[$idx]) {
                        // Set line width, revert it to normal at the end
                        ImageSetThickness($this->img, $this->line_widths[$idx]);

                        // Select the color:
                        $this->GetDataColor($row, $idx, $gcvars, $data_color);

                        if ($this->line_styles[$idx] == 'dashed') {
                            $this->SetDashedStyle($data_color);
                            $data_color = IMG_COLOR_STYLED;
                        }
                        ImageLine($this->img, $lastx[$idx], $lasty[$idx],
                                  $x_now_pixels, $lasty[$idx], $data_color);
                        ImageLine($this->img, $x_now_pixels, $lasty[$idx],
                                  $x_now_pixels, $y_now_pixels, $data_color);
                    }

                    // Draw data value labels?
                    if ($do_dvls) {
                        $this->DrawDataValueLabel('y', $x_now, $y_now, $y_now, $dvl_h_align, $dvl_v_align,
                                                  $dvl_x_off, $dvl_y_off);
                    }

                    $lastx[$idx] = $x_now_pixels;
                    $lasty[$idx] = $y_now_pixels;
                    $start_lines[$idx] = TRUE;
                } elseif ($this->draw_broken_lines) {  // Y data missing, leave a gap.
                    $start_lines[$idx] = FALSE;
                }
            }
        }   // end while

        ImageSetThickness($this->img, 1);
        return TRUE;
    }

    /*
     * Draw a Bar chart
     * Supports text-data format, with each row in the form array(label, y1, y2, y3, ...)
     * Horizontal bars (text-data-yx format) are sent to DrawHorizBars() instead.
     */
    protected function DrawBars()
    {
        if (!$this->CheckDataType('text-data, text-data-yx'))
            return FALSE;
        if ($this->datatype_swapped_xy)
            return $this->DrawHorizBars();
        $this->CalcBarWidths(FALSE, TRUE); // Calculate bar widths for unstacked, vertical

        // This is the X offset from the bar group's label center point to the left side of the first bar
        // in the group. See also CalcBarWidths above.
        $x_first_bar = ($this->data_columns * $this->record_bar_width) / 2 - $this->bar_adjust_gap;

        $gcvars = array(); // For GetBarColors, which initializes and uses this.

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            $x_now_pixels = $this->xtr(0.5 + $row);         // Place text-data at X = 0.5, 1.5, 2.5, etc...

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels);

            // Lower left X of first bar in the group:
            $x1 = $x_now_pixels - $x_first_bar;

            // Draw the bars in the group:
            for ($idx = 0; $record < $this->num_recs[$row]; $record++, $idx++) {
                if (is_numeric($this->data[$row][$record])) {       // Allow for missing Y data
                    $y = $this->data[$row][$record];
                    $x2 = $x1 + $this->actual_bar_width;

                    if (($upgoing_bar = $y >= $this->x_axis_position)) {
                        $y1 = $this->ytr($y);
                        $y2 = $this->x_axis_y_pixels;
                    } else {
                        $y1 = $this->x_axis_y_pixels;
                        $y2 = $this->ytr($y);
                    }

                    // Select the colors:
                    $this->GetBarColors($row, $idx, $gcvars, $data_color, $alt_color);

                    // Draw the bar, and the shade or border:
                    $this->DrawBar($x1, $y1, $x2, $y2, $data_color, $alt_color);

                    // Draw optional data labels above the bars (or below, for negative values).
                    if ( $this->y_data_label_pos == 'plotin') {
                        if ($upgoing_bar) {
                          $v_align = 'bottom';
                          $y_offset = -5 - $this->shading;
                        } else {
                          $v_align = 'top';
                          $y_offset = 2;
                        }
                        $this->DrawDataValueLabel('y', $row+0.5, $y, $y, 'center', $v_align,
                                ($idx + 0.5) * $this->record_bar_width - $x_first_bar, $y_offset);
                    }
                }
                // Step to next bar in group:
                $x1 += $this->record_bar_width;
            }   // end for
        }   // end for
        return TRUE;
    }

    /*
     * Draw a Horizontal Bar chart
     * Supports only text-data-yx format, with each row in the form array(label, x1, x2, x3, ...)
     * Note that the data values are X not Y, and the bars are drawn horizontally.
     * This is called from DrawBars, which has already checked the data type.
     */
    protected function DrawHorizBars()
    {
        $this->CalcBarWidths(FALSE, FALSE); // Calculate bar widths for unstacked, vertical

        // This is the Y offset from the bar group's label center point to the bottom of the first bar
        // in the group. See also CalcBarWidths above.
        $y_first_bar = ($this->data_columns * $this->record_bar_width) / 2 - $this->bar_adjust_gap;

        $gcvars = array(); // For GetBarColors, which initializes and uses this.

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            $y_now_pixels = $this->ytr(0.5 + $row);         // Place bars at Y=0.5, 1.5, 2.5, etc...

            if ($this->y_data_label_pos != 'none')          // Draw Y Data Labels?
                $this->DrawYDataLabel($this->data[$row][0], $y_now_pixels);

            // Lower left Y of first bar in the group:
            $y1 = $y_now_pixels + $y_first_bar;

            // Draw the bars in the group:
            for ($idx = 0; $record < $this->num_recs[$row]; $record++, $idx++) {
                if (is_numeric($this->data[$row][$record])) {       // Allow for missing X data
                    $x = $this->data[$row][$record];
                    $y2 = $y1 - $this->actual_bar_width;

                    if (($rightwards_bar = $x >= $this->y_axis_position)) {
                        $x1 = $this->xtr($x);
                        $x2 = $this->y_axis_x_pixels;
                    } else {
                        $x1 = $this->y_axis_x_pixels;
                        $x2 = $this->xtr($x);
                    }

                    // Select the colors:
                    $this->GetBarColors($row, $idx, $gcvars, $data_color, $alt_color);

                    // Draw the bar, and the shade or border:
                    $this->DrawBar($x1, $y1, $x2, $y2, $data_color, $alt_color);

                    // Draw optional data labels to the right of the bars (or left, if the bar
                    // goes left of the Y axis line).
                    if ($this->x_data_label_pos == 'plotin') {
                        if ($rightwards_bar) {
                          $h_align = 'left';
                          $x_offset = 5 + $this->shading;
                        } else {
                          $h_align = 'right';
                          $x_offset = -2;
                        }
                        $this->DrawDataValueLabel('x', $x, $row+0.5, $x, $h_align, 'center',
                                $x_offset, $y_first_bar - ($idx + 0.5) * $this->record_bar_width);
                    }

                }
                // Step to next bar in group:
                $y1 -= $this->record_bar_width;
            }   // end for
        }   // end for

        return TRUE;
    }

    /*
     * Draw a Stacked Bar chart
     * Supports text-data format, with each row in the form array(label, y1, y2, y3, ...)
     * Horizontal stacked bars (text-data-yx format) are sent to DrawHorizStackedBars() instead.
     * Original stacked bars idea by Laurent Kruk < lolok at users.sourceforge.net >
     */
    protected function DrawStackedBars()
    {
        if (!$this->CheckDataType('text-data, text-data-yx'))
            return FALSE;
        if ($this->datatype_swapped_xy)
            return $this->DrawHorizStackedBars();
        $this->CalcBarWidths(TRUE, TRUE); // Calculate bar widths for stacked, vertical

        // This is the X offset from the bar's label center point to the left side of the bar.
        $x_first_bar = $this->record_bar_width / 2 - $this->bar_adjust_gap;

        $gcvars = array(); // For GetBarColors, which initializes and uses this.

        // Determine if any data labels are on:
        $data_labels_within = ($this->y_data_label_pos == 'plotstack');
        $data_labels_end = $data_labels_within || ($this->y_data_label_pos == 'plotin');
        $data_label_y_offset = -5 - $this->shading; // For upward labels only.

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            $x_now_pixels = $this->xtr(0.5 + $row);         // Place text-data at X = 0.5, 1.5, 2.5, etc...

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels);

            // Determine bar direction based on 1st non-zero value. Note the bar direction is
            // based on zero, not the axis value.
            $n_recs = $this->num_recs[$row];
            $upward = TRUE; // Initialize this for the case of all segments = 0
            for ($i = $record; $i < $n_recs; $i++) {
                if (is_numeric($this_y = $this->data[$row][$i]) && $this_y != 0) {
                    $upward = ($this_y > 0);
                    break;
                }
            }

            $x1 = $x_now_pixels - $x_first_bar;  // Left X of bars in this stack
            $x2 = $x1 + $this->actual_bar_width; // Right X of bars in this stack
            $wy1 = 0;                            // World coordinates Y1, current sum of values
            $wy2 = $this->x_axis_position;       // World coordinates Y2, last drawn value

            // Draw bar segments and labels in this stack.
            $first = TRUE;
            for ($idx = 0; $record < $n_recs; $record++, $idx++) {

                // Skip missing Y values. Process Y=0 values due to special case of moved axis.
                if (is_numeric($this_y = $this->data[$row][$record])) {

                    $wy1 += $this_y;    // Keep the running total for this bar stack

                    // Draw nothing if this segment would not increase the bar height.
                    // Upward bars: draw if wy1>wy2.  Downward bars: Draw if wy1<wy2.
                    if (($wy1 < $wy2) XOR $upward) {

                        $y1 = $this->ytr($wy1); // Convert to device coordinates. $y1 is outermost value.
                        $y2 = $this->ytr($wy2); // $y2 is innermost (closest to axis).

                        // Select the colors:
                        $this->GetBarColors($row, $idx, $gcvars, $data_color, $alt_color);

                        // Draw the bar, and the shade or border:
                        $this->DrawBar($x1, $y1, $x2, $y2, $data_color, $alt_color,
                            // Only shade the top for upward bars, or the first segment of downward bars:
                            $upward || $first, TRUE);

                        // Draw optional data label for this bar segment just inside the end.
                        // Text value is the current Y, but position is the cumulative Y.
                        // The label is only drawn if it fits in the segment height |y2-y1|.
                        if ($data_labels_within) {
                            $this->DrawDataValueLabel('y', $row+0.5, $wy1, $this_y,
                                                      'center', $upward ? 'top' : 'bottom',
                                                      0, $upward ? 3 : -3, NULL, abs($y1 - $y2));
                        }
                        // Mark the new end of the bar, conditional on segment height > 0.
                        $wy2 = $wy1;
                        $first = FALSE;
                    }
                }
            }   // end for

            // Draw optional data label above the bar with the total value.
            // Value is wy1 (total value), but position is wy2 (end of the bar stack).
            // These differ only with wrong-direction segments, or a stack completely clipped by the axis.
            if ($data_labels_end) {
                $this->DrawDataValueLabel('y', $row+0.5, $wy2, $wy1, 'center', $upward ? 'bottom' : 'top',
                                          0, $upward ? $data_label_y_offset : 5);
            }
        }   // end for
        return TRUE;
    }

    /*
     * Draw a Horizontal Stacked Bar chart
     * Supports only text-data-yx format, with each row in the form array(label, x1, x2, x3, ...)
     * Note that the data values are X not Y, and the bars are drawn horizontally.
     * This is called from DrawStackedBars, which has already checked the data type.
     */
    protected function DrawHorizStackedBars()
    {
        $this->CalcBarWidths(TRUE, FALSE); // Calculate bar widths for stacked, horizontal

        // This is the Y offset from the bar's label center point to the bottom of the bar
        $y_first_bar = $this->record_bar_width / 2 - $this->bar_adjust_gap;

        $gcvars = array(); // For GetBarColors, which initializes and uses this.

        // Determine if any data labels are on:
        $data_labels_within = ($this->x_data_label_pos == 'plotstack');
        $data_labels_end = $data_labels_within || ($this->x_data_label_pos == 'plotin');
        $data_label_x_offset = 5 + $this->shading; // For rightward labels only

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            $y_now_pixels = $this->ytr(0.5 + $row);         // Place bars at Y=0.5, 1.5, 2.5, etc...

            if ($this->y_data_label_pos != 'none')          // Draw Y Data labels?
                $this->DrawYDataLabel($this->data[$row][0], $y_now_pixels);

            // Determine bar direction based on 1st non-zero value. Note the bar direction is
            // based on zero, not the axis value.
            $n_recs = $this->num_recs[$row];
            $rightward = TRUE; // Initialize this for the case of all segments = 0
            for ($i = $record; $i < $n_recs; $i++) {
                if (is_numeric($this_x = $this->data[$row][$i]) && $this_x != 0) {
                    $rightward = ($this_x > 0);
                    break;
                }
            }

            // Lower left and upper left Y of the bars in this stack:
            $y1 = $y_now_pixels + $y_first_bar;  // Lower Y of bars in this stack
            $y2 = $y1 - $this->actual_bar_width; // Upper Y of bars in this stack
            $wx1 = 0;                            // World coordinates X1, current sum of values
            $wx2 = $this->y_axis_position;       // World coordinates X2, last drawn value

            // Draw bar segments and labels in this stack.
            $first = TRUE;
            for ($idx = 0; $record < $this->num_recs[$row]; $record++, $idx++) {

                // Skip missing X values. Process Y=0 values due to special case of moved axis.
                if (is_numeric($this_x = $this->data[$row][$record])) {

                    $wx1 += $this_x;  // Keep the running total for this bar stack

                    // Draw nothing if this segment would not increase the bar length.
                    // Rightward bars: draw if wx1>wx2. Leftward bars: Draw if wx1<wx2.
                    if (($wx1 < $wx2) XOR $rightward) {

                        $x1 = $this->xtr($wx1); // Convert to device coordinates. $x1 is outermost value.
                        $x2 = $this->xtr($wx2); // $x2 is innermost (closest to axis).

                        // Select the colors:
                        $this->GetBarColors($row, $idx, $gcvars, $data_color, $alt_color);

                        // Draw the bar, and the shade or border:
                        $this->DrawBar($x1, $y1, $x2, $y2, $data_color, $alt_color,
                            // Only shade the side for rightward bars, or the first segment of leftward bars:
                            TRUE, $rightward || $first);
                        // Draw optional data label for this bar segment just inside the end.
                        // Text value is the current X, but position is the cumulative X.
                        // The label is only drawn if it fits in the segment width |x2-x1|.
                        if ($data_labels_within) {
                            $this->DrawDataValueLabel('x', $wx1, $row+0.5, $this_x,
                                                      $rightward ? 'right' : 'left', 'center',
                                                      $rightward ? -3 : 3, 0, abs($x1 - $x2), NULL);
                        }
                        // Mark the new end of the bar, conditional on segment width > 0.
                        $wx2 = $wx1;
                        $first = FALSE;
                    }
                }
            }   // end for

            // Draw optional data label right of the bar with the total value.
            // Value is wx1 (total value), but position is wx2 (end of the bar stack).
            // These differ only with wrong-direction segments, or a stack completely clipped by the axis.
            if ($data_labels_end) {
                $this->DrawDataValueLabel('x', $wx2, $row+0.5, $wx1, $rightward ? 'left' : 'right', 'center',
                                          $rightward ? $data_label_x_offset : -5, 0);
            }
        }   // end for
        return TRUE;
    }

    /*
     * Draw a financial "Open/High/Low/Close" (OHLC) plot, including candlestick plots.
     * Data format can be text-data (label, Yo, Yh, Yl, Yc) or data-data (label, X, Yo, Yh, Yl, Yc).
     * Yo="Opening price", Yc="Closing price", Yl="Low price", Yh="High price".
     * Each row must have exactly 4 Y values. Indicate a missing point using empty strings for each Yx.
     * There are 3 subtypes, selected by $draw_candles and $always_fill.
     *   $draw_candles  $always_fill  Description:
     *    FALSE          N/A          A basic OHLC chart with a vertical line for price range, horizontal
     *                                tick marks on left for opening price and right for closing price.
     *    TRUE           FALSE        A candlestick plot with filled body indicating close down, outline
     *                                for closing up, and vertical wicks for low and high prices.
     *    TRUE           TRUE         A candlestick plot where the candle bodies are always filled.
     * These map to 3 plot types per the $plots[] array.
     *
     * Data color usage:                        If closes down:   If closes up or unchanged:
     *    Candlestick body, ohlc range line:      color 0           color 1
     *    Candlestick wicks, ohlc tick marks:     color 2           color 3
     * There are three member variables that control the width (candlestick body or tick marks):
     *     ohlc_max_width, ohlc_min_width, ohlc_frac_width
     * (There is no API to change them at this time.)
     */
    protected function DrawOHLC($draw_candles, $always_fill = FALSE)
    {
        if (!$this->CheckDataType('text-data, data-data'))
            return FALSE;
        if ($this->data_columns != 4) // early error check (more inside the loop)
            return $this->PrintError("DrawOHLC(): rows must have 4 values.");

        // Assign name of GD function to draw candlestick bodies for stocks that close up.
        $draw_body_close_up = $always_fill ? 'imagefilledrectangle' : 'imagerectangle';

        // These 3 variables control the calculation of the half-width of the candle body, or length of
        // the tick marks. This is scaled based on the plot density, but within tight limits.
        $min_width = isset($this->ohlc_min_width) ? $this->ohlc_min_width : 2;
        $max_width = isset($this->ohlc_max_width) ? $this->ohlc_max_width : 8;
        $width_factor = isset($this->ohlc_frac_width) ? $this->ohlc_frac_width : 0.3;
        $dw = max($min_width, min($max_width,
                     (int)($width_factor * $this->plot_area_width / $this->num_data_rows)));

        // Get line widths to use: index 0 for body/stroke, 1 for wick/tick.
        list($body_thickness, $wick_thickness) = $this->line_widths;

        $gcvars = array(); // For GetDataColor, which initializes and uses this.

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            if ($this->datatype_implied)                    // Implied X values?
                $x_now = 0.5 + $row;                        // Place text-data at X = 0.5, 1.5, 2.5, etc...
            else
                $x_now = $this->data[$row][$record++];      // Read it, advance record index

            $x_now_pixels = $this->xtr($x_now);             // Convert X to device coordinates
            $x_left = $x_now_pixels - $dw;
            $x_right = $x_now_pixels + $dw;

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels, $row);

            // Each row must have 4 values, but skip rows with non-numeric entries.
            if ($this->num_recs[$row] - $record != 4) {
                return $this->PrintError("DrawOHLC(): row $row must have 4 values.");
            }
            if (!is_numeric($yo = $this->data[$row][$record++])
             || !is_numeric($yh = $this->data[$row][$record++])
             || !is_numeric($yl = $this->data[$row][$record++])
             || !is_numeric($yc = $this->data[$row][$record++])) {
                continue;
            }

            // Set device coordinates for each value and direction flag:
            $yh_pixels = $this->ytr($yh);
            $yl_pixels = $this->ytr($yl);
            $yc_pixels = $this->ytr($yc);
            $yo_pixels = $this->ytr($yo);
            $closed_up = $yc >= $yo;

            // Get data colors and line thicknesses:
            if ($closed_up) {
                $this->GetDataColor($row, 1, $gcvars, $body_color); // Color 1 for body, closing up
                $this->GetDataColor($row, 3, $gcvars, $ext_color);  // Color 3 for wicks/ticks
            } else {
                $this->GetDataColor($row, 0, $gcvars, $body_color); // Color 0 for body, closing down
                $this->GetDataColor($row, 2, $gcvars, $ext_color);  // Color 2 for wicks/ticks
            }
            imagesetthickness($this->img, $body_thickness);

            if ($draw_candles) {
                // Note: Unlike ImageFilledRectangle, ImageRectangle 'requires' its arguments in
                // order with upper left corner first.
                if ($closed_up) {
                    $yb1_pixels = $yc_pixels; // Upper body Y
                    $yb2_pixels = $yo_pixels; // Lower body Y
                    $draw_body = $draw_body_close_up;
                    // Avoid a PHP/GD bug resulting in "T"-shaped ends to zero height unfilled rectangle:
                    if ($yb1_pixels == $yb2_pixels)
                        $draw_body = 'imagefilledrectangle';
                } else {
                    $yb1_pixels = $yo_pixels;
                    $yb2_pixels = $yc_pixels;
                    $draw_body = 'imagefilledrectangle';
                }

                // Draw candle body
                $draw_body($this->img, $x_left, $yb1_pixels, $x_right, $yb2_pixels, $body_color);

                // Draw upper and lower wicks, if they have height. (In device coords, that's dY<0)
                imagesetthickness($this->img, $wick_thickness);
                if ($yh_pixels < $yb1_pixels) {
                    imageline($this->img, $x_now_pixels, $yb1_pixels, $x_now_pixels, $yh_pixels, $ext_color);
                }
                if ($yl_pixels > $yb2_pixels) {
                    imageline($this->img, $x_now_pixels, $yb2_pixels, $x_now_pixels, $yl_pixels, $ext_color);
                }
            } else {
                // Basic OHLC
                imageline($this->img, $x_now_pixels, $yl_pixels, $x_now_pixels, $yh_pixels, $body_color);
                imagesetthickness($this->img, $wick_thickness);
                imageline($this->img, $x_left, $yo_pixels, $x_now_pixels, $yo_pixels, $ext_color);
                imageline($this->img, $x_right, $yc_pixels, $x_now_pixels, $yc_pixels, $ext_color);
            }
            imagesetthickness($this->img, 1);
        }
        return TRUE;
    }

    /*
     * Draw a bubble chart, which is a scatter plot with bubble size showing the Z value.
     * Supported data type is data-data-xyz with rows of (label, X, Y1, Z1, ...)
     * with multiple data sets (Y, Z pairs) supported.
     * Bubble sizes are scaled per the min_z and max_z calculated in FindDataLimits.
     */
    protected function DrawBubbles()
    {
        if (!$this->CheckDataType('data-data-xyz'))
            return FALSE;
        if ($this->data_columns == 0) return TRUE; // No data to plot; prevents error on min_z/max_z.

        $gcvars = array(); // For GetDataColor, which initializes and uses this.

        // Bubble size limits can be set with class variables or calculated.
        $min_bubble_size = isset($this->bubbles_min_size) ? $this->bubbles_min_size : 6;
        if (isset($this->bubbles_max_size)) {
            $max_bubble_size = $this->bubbles_max_size;
        } else {
            $max_bubble_size = min($this->plot_area_width, $this->plot_area_height) / 12;
        }

        // Calculate bubble scale parameters. Bubble_size(z) = $f_size * $z + $b_size
        if ($this->max_z <= $this->min_z) {   // Regressive case, no Z range.
            $f_size = 0;
            $b_size = ($max_bubble_size + $min_bubble_size) / 2; // Use average size of all bubbles
        } else {
            $f_size = ($max_bubble_size - $min_bubble_size) / ($this->max_z - $this->min_z);
            $b_size = $max_bubble_size - $f_size * $this->max_z;
        }

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $rec = 1;                    // Skip record #0 (data label)
            $x = $this->xtr($this->data[$row][$rec++]); // Get X value from data array.

            // Draw X Data labels?
            if ($this->x_data_label_pos != 'none')
                $this->DrawXDataLabel($this->data[$row][0], $x, $row);

            // Proceed with Y,Z values
            for ($idx = 0; $rec < $this->num_recs[$row]; $rec += 2, $idx++) {
                if (is_numeric($this->data[$row][$rec])) {              // Allow for missing Y data
                    $y = $this->ytr((double)$this->data[$row][$rec]);
                    $z = (double)$this->data[$row][$rec+1]; // Z is required if Y is present.
                    $size = (int)($f_size * $z + $b_size);  // Calculate bubble size

                    // Select the color:
                    $this->GetDataColor($row, $idx, $gcvars, $data_color);

                    // Draw the bubble:
                    ImageFilledEllipse($this->img, $x, $y, $size, $size, $data_color);
                }
            }
        }
        return TRUE;
    }

    /*
     * Draw the graph.
     * This is the function that performs the actual drawing, after all
     * the parameters and data are set up.
     * It also outputs the finished image, unless told not to.
     * Note: It is possible for this to be called multiple times.
     */
    function DrawGraph()
    {
        // Test for missing image, missing data, empty data:
        if (!$this->CheckDataArray())
            return FALSE; // Error message already reported.

        // Set defaults then import plot type configuration:
        $draw_axes = TRUE;
        $draw_arg = array(); // Default is: no arguments to the drawing function
        extract(PHPlot::$plots[$this->plot_type]);

        // Allocate colors for the plot:
        $this->SetColorIndexes();

        // Get maxima and minima for scaling:
        if (!$this->FindDataLimits())
            return FALSE;

        // Set plot area world values (plot_max_x, etc.):
        if (!$this->CalcPlotAreaWorld())
            return FALSE;

        // Calculate X and Y axis positions in World Coordinates:
        $this->CalcAxisPositions();

        // Process label-related parameters:
        $this->CheckLabels();

        // Apply grid defaults:
        $this->CalcGridSettings();

        // Calculate the plot margins, if needed.
        // For pie charts, set the $maximize argument to maximize space usage.
        $this->CalcMargins(!$draw_axes);

        // Calculate the actual plot area in device coordinates:
        $this->CalcPlotAreaPixels();

        // Calculate the mapping between world and device coordinates:
        $this->CalcTranslation();

        // Pad color and style arrays to fit records per group:
        $this->PadArrays();
        $this->DoCallback('draw_setup');

        $this->DrawBackground();
        $this->DrawImageBorder();
        $this->DoCallback('draw_image_background');

        $this->DrawPlotAreaBackground();
        $this->DoCallback('draw_plotarea_background', $this->plot_area);

        $this->DrawTitle();
        if ($draw_axes) {  // If no axes (pie chart), no axis titles either
            $this->DrawXTitle();
            $this->DrawYTitle();
        }
        $this->DoCallback('draw_titles');

        if ($draw_axes && ! $this->grid_at_foreground) {   // Usually one wants grids to go back, but...
            $this->DrawYAxis();     // Y axis must be drawn before X axis (see DrawYAxis())
            $this->DrawXAxis();
            $this->DoCallback('draw_axes');
        }

        // Call the plot-type drawing method:
        call_user_func_array(array($this, $draw_method), $draw_arg);
        $this->DoCallback('draw_graph', $this->plot_area);

        if ($draw_axes && $this->grid_at_foreground) {   // Usually one wants grids to go back, but...
            $this->DrawYAxis();     // Y axis must be drawn before X axis (see DrawYAxis())
            $this->DrawXAxis();
            $this->DoCallback('draw_axes');
        }

        if ($draw_axes) {
            $this->DrawPlotBorder();
            $this->DoCallback('draw_border');
        }

        if ($this->legend) {
            $this->DrawLegend();
            $this->DoCallback('draw_legend');
        }
        $this->DoCallback('draw_all', $this->plot_area);

        if ($this->print_image && !$this->PrintImage())
            return FALSE;

        return TRUE;
    }

/////////////////////////////////////////////
//////////////////         DEPRECATED METHODS
/////////////////////////////////////////////

    /*
     * Note on deprecated methods - as these pre-date the PHPlot Reference
     * Manual, and there is minimal documentation about them, I have neither
     * removed them nor changed them. They are not tested or documented, and
     * should not be used.
     */

    /*
     * Deprecated, use SetYTickPos()
     */
    function SetDrawVertTicks($which_dvt)
    {
        if ($which_dvt != 1)
            $this->SetYTickPos('none');
        return TRUE;
    }

    /*
     * Deprecated, use SetXTickPos()
     */
    function SetDrawHorizTicks($which_dht)
    {
        if ($which_dht != 1)
           $this->SetXTickPos('none');
        return TRUE;
    }

    /*
     * Deprecated - use SetNumXTicks()
     */
    function SetNumHorizTicks($n)
    {
        return $this->SetNumXTicks($n);
    }

    /*
     * Deprecated - use SetNumYTicks()
     */
    function SetNumVertTicks($n)
    {
        return $this->SetNumYTicks($n);
    }

    /*
     * Deprecated - use SetXTickIncrement()
     */
    function SetHorizTickIncrement($inc)
    {
        return $this->SetXTickIncrement($inc);
    }

    /*
     * Deprecated - use SetYTickIncrement()
     */
    function SetVertTickIncrement($inc)
    {
        return $this->SetYTickIncrement($inc);
    }

    /*
     * Deprecated - use SetYTickPos()
     */
    function SetVertTickPosition($which_tp)
    {
        return $this->SetYTickPos($which_tp);
    }

    /*
     * Deprecated - use SetXTickPos()
     */
    function SetHorizTickPosition($which_tp)
    {
        return $this->SetXTickPos($which_tp);
    }

    /*
     * Deprecated - use SetFont()
     */
    function SetTitleFontSize($which_size)
    {
        return $this->SetFont('title', $which_size);
    }

    /*
     * Deprecated - use SetFont()
     */
    function SetAxisFontSize($which_size)
    {
        $this->SetFont('x_label', $which_size);
        $this->SetFont('y_label', $which_size);
    }

    /*
     * Deprecated - use SetFont()
     */
    function SetSmallFontSize($which_size)
    {
        return $this->SetFont('generic', $which_size);
    }

    /*
     * Deprecated - use SetFont()
     */
    function SetXLabelFontSize($which_size)
    {
        return $this->SetFont('x_title', $which_size);
    }

    /*
     * Deprecated - use SetFont()
     */
    function SetYLabelFontSize($which_size)
    {
        return $this->SetFont('y_title', $which_size);
    }

    /*
     * Deprecated - use SetXTitle()
     */
    function SetXLabel($which_xlab)
    {
        return $this->SetXTitle($which_xlab);
    }

    /*
     * Deprecated - use SetYTitle()
     */
    function SetYLabel($which_ylab)
    {
        return $this->SetYTitle($which_ylab);
    }

    /*
     * Deprecated - use SetXTickLength() and SetYTickLength() instead.
     */
    function SetTickLength($which_tl)
    {
        $this->SetXTickLength($which_tl);
        $this->SetYTickLength($which_tl);
        return TRUE;
    }

    /*
     * Deprecated - use SetYLabelType()
     */
    function SetYGridLabelType($which_yglt)
    {
        return $this->SetYLabelType($which_yglt);
    }

    /*
     * Deprecated - use SetXLabelType()
     */
    function SetXGridLabelType($which_xglt)
    {
        return $this->SetXLabelType($which_xglt);
    }
    /*
     * Deprecated - use SetYTickLabelPos()
     */
    function SetYGridLabelPos($which_yglp)
    {
        return $this->SetYTickLabelPos($which_yglp);
    }
    /*
     * Deprecated - use SetXTickLabelPos()
     */
    function SetXGridLabelPos($which_xglp)
    {
        return $this->SetXTickLabelPos($which_xglp);
    }

    /*
     * Deprecated - use SetXtitle()
     */
    function SetXTitlePos($xpos)
    {
        $this->x_title_pos = $xpos;
        return TRUE;
    }

    /*
     * Deprecated - use SetYTitle()
     */
    function SetYTitlePos($xpos)
    {
        $this->y_title_pos = $xpos;
        return TRUE;
    }

    /*
     * Deprecated - use SetXDataLabelPos()
     */
    function SetDrawXDataLabels($which_dxdl)
    {
        if ($which_dxdl == '1' )
            $this->SetXDataLabelPos('plotdown');
        else
            $this->SetXDataLabelPos('none');
    }

    /*
     * Deprecated - use SetPlotAreaPixels()
     */
    function SetNewPlotAreaPixels($x1, $y1, $x2, $y2)
    {
        return $this->SetPlotAreaPixels($x1, $y1, $x2, $y2);
    }

    /*
     * Deprecated - use SetLineWidths().
     */
    function SetLineWidth($which_lw)
    {

        $this->SetLineWidths($which_lw);

        if (!$this->error_bar_line_width) {
            $this->SetErrorBarLineWidth($which_lw);
        }
        return TRUE;
    }

    /*
     * Deprecated - use SetPointShapes().
     */
    function SetPointShape($which_pt)
    {
        $this->SetPointShapes($which_pt);
        return TRUE;
    }

    /*
     * Deprecated - use SetPointSizes().
     */
    function SetPointSize($which_ps)
    {
        $this->SetPointSizes($which_ps);
        return TRUE;
    }
}

/*
 * The PHPlot_truecolor class extends PHPlot to use GD truecolor images.
 */

class PHPlot_truecolor extends PHPlot
{
    /*
     * PHPlot Truecolor variation constructor: Create a PHPlot_truecolor object and initialize it.
     * Note this does NOT call the parent (PHPlot) constructor. It duplicates the code here.
     * Everything is the same as the PHPlot constructor except for imagecreatetruecolor.
     *
     * Parameters are the same as PHPlot:
     *   $which_width : Image width in pixels.
     *   $which_height : Image height in pixels.
     *   $which_output_file : Filename for output.
     *   $which_input_file : Path to a file to be used as background.
     */
    function __construct($which_width=600, $which_height=400, $which_output_file=NULL, $which_input_file=NULL)
    {
        $this->SetRGBArray($this->color_array);

        if ($which_output_file)
            $this->SetOutputFile($which_output_file);

        if ($which_input_file) {
            $this->SetInputFile($which_input_file);
        } else {
            $this->image_width = $which_width;
            $this->image_height = $which_height;

            $this->img = imagecreatetruecolor($this->image_width, $this->image_height);
            if (! $this->img)
                return $this->PrintError('PHPlot_truecolor(): Could not create image resource.');
        }

        $this->SetDefaultStyles();
        $this->SetDefaultFonts();
    }
}