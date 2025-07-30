<?php

namespace App\Enums;

enum GameStatus: string
{
    case Scheduled = 'scheduled';   // 試合前
    case Ongoing   = 'ongoing';     // 試合中
    case Finished  = 'finished';    // 終了
}
