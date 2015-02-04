<?php namespace CmsCanvas\Content;

use View, Config;
use CmsCanvas\Content\Entry\Builder as EntryBuilder;
use Intervention\Image\ImageManagerStatic as Image;

class Content {

    public function entries($config)
    {
        $entries = new EntryBuilder($config);
        $view = $entries->get();

        return $view;
    }

    /**
     * Resizes and caches an images to the specified dimensions
     *
     * @param string $source
     * @param int $width
     * @param int $height
     * @param bool $crop
     * @param array $additionalArguments
     * @return string
     */
    public function thumbnail($source, $width = null, $height = null, $crop = false, $additionalArguments = array())
    {
        if ($width == null || $height == null) {
            return $source;
        }

        $publicRootConfig = trim(Config::get('cmscanvas::config.public_root'), '/').'/';
        $thumbnailsConfig = trim(Config::get('cmscanvas::config.thumbnails'), '/').'/';

        $source = str_replace(asset(null), '', $source);
        $sourceImage = public_path($source);
        $thumbnailDirectory = public_path($thumbnailsConfig);

        $sourceModificationTime = @filemtime($sourceImage);

        if ($source == false || $sourceModificationTime === false) 
        {
            if (isset($additionalArguments['no_image']))
            {
                $additionalArguments['no_image'] = str_replace(asset(null), '', $additionalArguments['no_image']);
                $sourceImage = public_path($additionalArguments['no_image']);
                $sourceModificationTime = @filemtime($sourceImage);
            }
            else
            {
                return '';
            }
        }

        //The new generated filename we want
        $info = pathinfo($sourceImage);

        $filename = (isset($info['filename'])) ? $info['filename'] : '';
        $extension = (isset($info['extension'])) ? $info['extension'] : '';
        $dirname = (isset($info['dirname'])) ? $info['dirname'] : '';

        $thumbnailFilename = md5($dirname.'/'.$filename.'.'.$extension).'-'.$filename.'-'.$width.'x'.$height;

        if ($crop == true) 
        {
            $thumbnailFilename .= '-cropped';
        }

        $thumbnailFilename .= '.'.$extension;
        $destinationImage = $thumbnailDirectory.$thumbnailFilename;
        $destinationModificationTime = @filemtime($destinationImage);

        if ($sourceModificationTime !== false 
            && ($destinationModificationTime === false || $destinationModificationTime < $sourceModificationTime)
        ) 
        {
            $image = Image::make($sourceImage);

            if ($crop == true) 
            {
                $image->fit($width, $height, function ($constraint) {
                    $constraint->upsize();
                }, 'center');
            } else {
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $image->save($destinationImage);
        }

        return asset($thumbnailsConfig.$thumbnailFilename);
    }

}