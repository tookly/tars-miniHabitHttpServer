<?php
/**
 * Created by PhpStorm.
 * User: liangchen
 * Date: 2018/5/8
 * Time: 下午2:42.
 */

namespace HttpServer\controller;

use HttpServer\component\Redis;
use HttpServer\component\Controller;

class TargetController extends Controller
{
    const TARGET_KEY = 'HASH:TARGET:%s';
    const TARGET_NOTE_KEY = 'ZSET:TARGET:NOTE:%s';
    const TARGET_SIGN_KEY = 'ZSET:TARGET:SIGN:%s';
    const TARGET_SIGN_LOG_KEY = 'LIST:TARGET:SIGN:LOG:%s:%s';
    
    /**
     * @throws \exception
     */
    public function actionInfo()
    {
        $targetId = $this->getGet('targetId', 1);
        $data = Redis::instance()->hGetAll(sprintf(self::TARGET_KEY, $targetId)) ?: null;
        $this->sendSuccess($data);
    }
    
    /**
     * @throws \exception
     */
    public function actionSet()
    {
        $data['targetId'] = $this->getPost('targetId', 1);
        $data['target'] = $this->getPost('target', '');
        if (empty($data['target']) || empty($data['targetId'])) {
            $this->sendParamErr();
        }
        Redis::instance()->hMSet(sprintf(self::TARGET_KEY, $data['targetId']), $data);
        $this->sendSuccess($data);
    }
    
    /**
     * @throws \exception
     */
    public function actionNotes()
    {
        $targetId = $this->getGet('targetId', 1);
        $lines = Redis::instance()->zRevRange(sprintf(self::TARGET_NOTE_KEY, $targetId), 0, -1, true);
        $notes = [];
        foreach ($lines as $dateline => $line) {
            $note = json_decode($line, true);
            if (empty($note)) {
                continue;
            }
            $notes[] = $note;
        }
        $data['notes'] = $notes;
        $this->sendSuccess($data);
    }
    
    /**
     * @throws \exception
     */
    public function actionNote()
    {
        $targetId = $this->getPost('targetId', 1);
        $note = $this->getPost('note', '');
        if (empty($note) || empty($targetId)) {
            $this->sendParamErr();
        }
        $dateline = time();
        $line = json_encode(['note' => $note, 'dateline' => $dateline]);
        Redis::instance()->zAdd(sprintf(self::TARGET_NOTE_KEY, $targetId), $dateline, $line);
        $this->sendSuccess();
    }
    
    /**
     * @throws \exception
     */
    public function actionSign()
    {
        $targetId = $this->getPost('targetId', 1);
        $unit = $this->getPost('unit', 1);
        if (empty($targetId) || empty($unit)) {
            $this->sendParamErr();
        }
        $date = date('Ymd', time());
        $data['count'] = Redis::instance()->zIncrBy(sprintf(self::TARGET_SIGN_KEY, $targetId), $unit, $date);
        
        $log['targetId'] = $targetId;
        $log['unit'] = $unit;
        $log['dateline'] = time();
        Redis::instance()->lpush(sprintf(self::TARGET_SIGN_LOG_KEY, $targetId, $date), json_encode($log));
        
        $this->sendSuccess($data);
    }
    
    /**
     * @throws \Exception
     */
    public function actionStatistics()
    {
        $targetId = $this->getGet('targetId', 1);
        $end = time();
        $start = $end - 86400 * 20;
        $endDate = date('Ymd', $end);
        $startDate = date('Ymd', $start);
        $records = Redis::instance()->zRevRangeByScore(sprintf(self::TARGET_SIGN_KEY, $targetId), $startDate, $endDate, true);
        $data = [];
        for($i = $start; $i <= $end; $i = $i - 86400) {
            $date = date('Ymd', $i);
            $tmp['date'] = $date;
            $tmp['times'] = $records[$date] ?? 0;
            $data[] = $tmp;
        }
        $this->sendSuccess($data);
    }
    
}
