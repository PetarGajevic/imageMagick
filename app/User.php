<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

public static function getAverageColorString(\Imagick $imagick) {

        $tshirtCrop = clone $imagick;
        $tshirtCrop->cropimage(100, 100, 90, 50);
        $stats = $tshirtCrop->getImageChannelStatistics();
        $averageRed = $stats[\Imagick::CHANNEL_RED]['mean'];
        $averageRed = intval(255 * $averageRed / \Imagick::getQuantum());
        $averageGreen = $stats[\Imagick::CHANNEL_GREEN]['mean'];
        $averageGreen = intval(255 * $averageGreen / \Imagick::getQuantum());
        $averageBlue = $stats[\Imagick::CHANNEL_BLUE]['mean'];
        $averageBlue = intval(255 * $averageBlue / \Imagick::getQuantum());
        $colorString = "rgb($averageRed, $averageGreen, $averageBlue)";
      
        return $colorString;
      }
  
}
