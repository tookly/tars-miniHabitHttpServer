<?php
/**
 * Created by PhpStorm.
 * User: liangchen
 * Date: 2018/5/8
 * Time: 下午2:42.
 */

namespace HttpServer\controller;

use HttpServer\component\HabitException;
use HttpServer\component\Redis;
use HttpServer\component\Controller;
use HttpServer\conf\Code;
use HttpServer\model\TargetModel;

class TargetController extends Controller
{
    const TARGET_KEY = 'HASH:TARGET:%s';
    const TARGET_NOTE_KEY = 'ZSET:TARGET:NOTE:%s';
    const TARGET_SIGN_KEY = 'ZSET:TARGET:SIGN:%s';
    const TARGET_SIGN_WEEK_KEY = 'ZSET:TARGET:WEEK:SIGN:%s';
    const TARGET_SIGN_LOG_KEY = 'LIST:TARGET:SIGN:LOG:%s:%s';

    public function actionIndex()
    {
        return TargetModel::getInfo($this->user);
    }
    
    /**
     * @throws \exception
     */
    public function actionSet()
    {
        if (!$this->isLogin()) {
            throw new HabitException(Code::LOGIN_ERROR);
        }
        $data['targetId'] = $this->user->userId;
        $data['target'] = $this->getPost('target', '');
        $data['dateline'] = time();
        if (empty($data['target']) || empty($data['targetId'])) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        Redis::instance()->hMSet(sprintf(self::TARGET_KEY, $data['targetId']), $data);
        return $data;
    }
    
    /**
     * @throws \exception
     */
    public function actionNotes()
    {
        if (!$this->isLogin()) {
            throw new HabitException(Code::LOGIN_ERROR);
        }
        $targetId = $this->user->userId;
        $lines = Redis::instance()->zRevRange(sprintf(self::TARGET_NOTE_KEY, $targetId), 0, -1, true);
        $notes = [];
        foreach ($lines as $line => $dateline) {
            $note = json_decode($line, true);
            if (empty($note)) {
                continue;
            }
            $notes[] = $note;
        }
        $data['notes'] = $notes;
        return $data;
    }
    
    /**
     * @throws \exception
     */
    public function actionNote()
    {
        if (!$this->isLogin()) {
            throw new HabitException(Code::LOGIN_ERROR);
        }
        $targetId = $this->user->userId;
        $note = $this->getPost('note', '');
        if (empty($note) || empty($targetId)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        $dateline = time();
        $line = json_encode(['note' => $note, 'dateline' => $dateline]);
        Redis::instance()->zAdd(sprintf(self::TARGET_NOTE_KEY, $targetId), $dateline, $line);
        $data = [
            "dateline" => $dateline,
            "value" => $note,
        ];
        return $data;
    }
    
    /**
     * @throws \exception
     */
    public function actionSign()
    {
        if (!$this->isLogin()) {
            throw new HabitException(Code::LOGIN_ERROR);
        }
        $targetId = $this->user->userId;
        $unit = $this->getPost('unit', 1);
        if (empty($targetId) || empty($unit)) {
            throw new HabitException(Code::ERROR_PARAMS);
        }
        $date = date('Ymd', time());
        $data['count'] = Redis::instance()->zIncrBy(sprintf(self::TARGET_SIGN_KEY, $targetId), $unit, $date);
        Redis::instance()->zIncrBy(sprintf(self::TARGET_SIGN_WEEK_KEY, $targetId), $unit, date('YW'));
        
        $log['targetId'] = $targetId;
        $log['unit'] = $unit;
        $log['dateline'] = time();
        Redis::instance()->lpush(sprintf(self::TARGET_SIGN_LOG_KEY, $targetId, $date), json_encode($log));
       
        return $data;
    }
    
    /**
     * 主态 和 客态
     * @throws \Exception
     */
    public function actionStatistics()
    {
        if (!$this->isLogin()) {
            throw new HabitException(Code::LOGIN_ERROR);
        }
        $targetId = $this->user->userId;
        
        // 处理日打卡数据
        $end = time();
        $start = $end - 86400 * 7;
        $day = [];
        for($i = $end; $i > $start; $i = $i - 86400) {
            $tmp['categories'] = date('m.d', $i);
            $tmp['data'] = (int)Redis::instance()->zScore(sprintf(self::TARGET_SIGN_KEY, $targetId), date('Ymd', $i));
            $day[] = $tmp;
        }
        $data['day']['title'] = '日打卡';
        $data['day']['data'] = array_column($day, 'data');
        $data['day']['categories'] = array_column($day, 'categories');
    
        $target = Redis::instance()->hGetAll(sprintf(self::TARGET_KEY, $targetId));
        $startWeek = date('YW', $target['dateline']);
        
        $end = date('YW');
        $start = $end - 3;
        $week = [];
        for($i = $end; $i > $start; $i = $i - 1) {
            $tmp['categories'] = 'W' . (int)($i - $startWeek + 1);
            $tmp['data'] = (int)Redis::instance()->zScore(sprintf(self::TARGET_SIGN_WEEK_KEY, $targetId), $i);
            $week[] = $tmp;
        }
        $data['week']['title'] = '周打卡';
        $data['week']['data'] = array_column($week, 'data');
        $data['week']['categories'] = array_column($week, 'categories');
        return $data;
    }
    
}
