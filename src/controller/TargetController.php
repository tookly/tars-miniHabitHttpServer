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
    const TARGET_NOTE_KEY = 'LIST:TARGET:NOTE:%s';
    const TARGET_SIGN_KEY = 'ZSET:TARGET:SIGN:%s';
    const TARGET_SIGN_LOG_KEY = 'LIST:TARGET:SIGN:LOG:%s:%s'; // 最好还是落db，先不加了
    
    /**
     * @throws \exception
     */
    public function actionInfo()
    {
        $targetId = $this->getGet('targetId');
        $data = Redis::instance()->hGetAll(sprintf(self::TARGET_KEY, $targetId)) ?: [];
        $this->sendSuccess($data);
    }
    
    /**
     * @throws \exception
     */
    public function actionSet()
    {
        $data['target'] = $this->getPost('target');
        $data['targetId'] = $this->getPost('targetId');
        if (empty($data['target']) || $data['targetId'] <= 0) {
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
        $targetId = $this->getGet('targetId');
        $lines = Redis::instance()->lRange(sprintf(self::TARGET_NOTE_KEY, $targetId), 0, -1);
        $notes = [];
        foreach ($lines as $line) {
            $note = json_decode($line, true);
            if (empty($note) || empty($note['line'] || empty($note['dateline']))) {
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
        $targetId = $this->getPost('targetId');
        $note = $this->getPost('note');
        if (empty($note) || $targetId <= 0) {
            $this->sendParamErr();
        }
        $line = json_encode(['note' => $note, 'dateline' => time()]);
        Redis::instance()->lPush(sprintf(self::TARGET_NOTE_KEY, $targetId), $line);
        $this->sendSuccess();
    }
    
    /**
     * @throws \exception
     */
    public function actionSign()
    {
        $targetId = $this->getPost('targetId');
        $unit = $this->getPost('unit', 1);
        if ($targetId <= 0) {
            $this->sendParamErr();
        }
        $date = date('Ymd', time());
        $data['count'] = Redis::instance()->zIncrBy(sprintf(self::TARGET_SIGN_KEY, $targetId), $unit, $date);
        
        // todo log
        $log['targetId'] = $targetId;
        $log['unit'] = $unit;
        $log['dateline'] = time();
        Redis::instance()->lpush(sprintf(self::TARGET_SIGN_LOG_KEY, $targetId, $date), json_encode($log));
        
        $this->sendSuccess($data);
    }
    
    public function actionStatistics()
    {
        $targetId = $this->getPost('targetId');
    }
}
