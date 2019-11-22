<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\widgets\mediaelement;


use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

class MediaElement extends Widget
{
    const TYPE_AUDIO = 'audio';
    const TYPE_VIDEO = 'video';
    protected $allowTypes = [
        'audio',
        'video',
    ];
    public    $width;
    public    $height;
    public    $file;
    public    $type       = self::TYPE_AUDIO;
    public    $modal;
    public    $autoplay   = false;
    public    $title      = false;

    public $options;

    private $defaultOptions = [
        'defaultVideoWidth'        => 480,             // if the <video width> is not specified, this is the default
        'defaultVideoHeight'       => 270,            // if the <video height> is not specified, this is the default
        'videoWidth'               => -1,                     // if set, overrides <video width>
        'videoHeight'              => -1,                    // if set, overrides <video height>
        'audioWidth'               => 700,                    // width of audio player
        'audioHeight'              => 50,                    // height of audio player
        'startVolume'              => 0.8,                   // initial volume when the player starts
        'loop'                     => true,                        // useful for <audio> player loops
        'enableAutosize'           => true,               // enables Flash and Silverlight to resize to content size
        'autoplay'                 => true,
        'features'                 => ['playpause', 'progress', 'current', 'duration', 'tracks', 'volume', 'fullscreen'], // the order of controls you want on the control bar (and other plugins below)
        'alwaysShowControls'       => true,          // Hide controls when playing and mouse is not over the video
        'iPadUseNativeControls'    => false,       // force iPad's native controls
        'iPhoneUseNativeControls'  => false,     // force iPhone's native controls
        'AndroidUseNativeControls' => false,    // force Android's native controls
        'alwaysShowHours'          => false,             // forces the hour marker (##:00:00)
        'showTimecodeFrameCount'   => false,      // show framecount in timecode (##:00:00:00)
        'framesPerSecond'          => 25,                // used when showTimecodeFrameCount is set to true
        'enableKeyboard'           => true,               // turns keyboard support on and off for this instance
        'pauseOtherPlayers'        => true,            // when this player starts, it will pause other players
        'keyActions'               => []                      // array of keyboard commands
    ];

    /**
     * @var array Default settings array
     */

    public function init()
    {

        $view = $this->getView();

        MediaElementAsset::register($view);
        $view->registerJs($this->getJs(), View::POS_READY);

        $this->defaultOptions = [
            'defaultVideoWidth'        => 480,             // if the <video width> is not specified, this is the default
            'defaultVideoHeight'       => 270,            // if the <video height> is not specified, this is the default
            'videoWidth'               => -1,                     // if set, overrides <video width>
            'videoHeight'              => -1,                    // if set, overrides <video height>
            'audioWidth'               => 700,                    // width of audio player
            'audioHeight'              => 30,                    // height of audio player
            'startVolume'              => 0.8,                   // initial volume when the player starts
            'loop'                     => false,                        // useful for <audio> player loops
            'enableAutosize'           => true,               // enables Flash and Silverlight to resize to content size
            'features'                 => ['playpause', 'progress', 'current', 'duration', 'tracks', 'volume', 'fullscreen'], // the order of controls you want on the control bar (and other plugins below)
            'alwaysShowControls'       => false,          // Hide controls when playing and mouse is not over the video
            'iPadUseNativeControls'    => false,       // force iPad's native controls
            'iPhoneUseNativeControls'  => false,     // force iPhone's native controls
            'AndroidUseNativeControls' => false,    // force Android's native controls
            'alwaysShowHours'          => false,             // forces the hour marker (##:00:00)
            'showTimecodeFrameCount'   => false,      // show framecount in timecode (##:00:00:00)
            'framesPerSecond'          => 25,                // used when showTimecodeFrameCount is set to true
            'enableKeyboard'           => true,               // turns keyboard support on and off for this instance
            'pauseOtherPlayers'        => true,            // when this player starts, it will pause other players
            'keyActions'               => []                      // array of keyboard commands
        ];
        parent::init();

    }

    public function run()
    {

        if ($this->modal) {
            $output = self::modal_view();
        } else {
            $output = self::view();
        }

        return $output;
    }

    public function embed_video()
    {

        $output = '<div class="embed-responsive embed-responsive-16by9">';

        $output .= Html::tag('video', '', [
            'src'      => $this->file,
            'width'    => $this->width,
            'height'   => $this->height,
            'id'       => $this->id,
            'controls' => "controls",
            'preload'  => "none",
        ]);

        $output .= '</div>';

        return $output;
    }

    public function embed_audio()
    {
        return Html::tag('audio', '', [
            'src'      => $this->file,
            'width'    => $this->width,
            'height'   => $this->height,
            'id'       => $this->id,
            //            'style' => 'background: white;',
            'controls' => "controls",
            'preload'  => "none",
        ]);

    }

    public function modal_view()
    {

        $modal_id = "modal_" . $this->id;

        $modal_btn = Html::a($this->settings['title'], null, [
            'class'       => "btn btn-default btn",
            'data-toggle' => "modal",
            'data-target' => "#" . $modal_id,
            'type'        => "button",
        ]);


        $output = '<div class="modal fade" id="' . $modal_id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';

        $output .= ($this->settings['title']) ? '<h4 class="modal-title" id="myModalLabel">' . $this->title . '</h4>' : ' ';
        $output .= '            </div>
                                <div class="modal-body" id="mediaModal' . $this->id . '">';
        $output .= $this->view();
        $output .= '            </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-dismiss="modal">Закрыть</button>
                                </div>
                            </div>
                        </div>
                    </div>';

        return ($this->settings['title']) ? $modal_btn . $output : $output;
    }

    public function view()
    {
        return $this->type == 'video' ? $this->embed_video() : $this->embed_audio();
    }

    private function getJs()
    {
        $js = [];

//        $this->playerOptions[
//                'defaultVideoWidth' => 480,
//                'defaultVideoHeight' => 270,
//            ]

//        $js [] = '$('#mediaModalw0').width()';
        //$js [] = 'var player = new MediaElementPlayer("#' . $this->id . '", []);';
        $js [] = '$("#' . $this->id . '").mediaelementplayer(' . Json::encode($this->defaultOptions) . ');';
//        $js [] = 'player.pause();';
//        $js [] = 'player.setSrc("mynewfile.mp4");';
//        $js [] = 'player.play();';

//        $js [] = 'var v = document.getElementsByTagName("video")[0];
//        $js [] = 'new MediaElement(v, {success: function(media) {';
//        $js [] = 'media.stop();';
//        $js [] = '}});';

        return implode("\n", $js);
    }

}