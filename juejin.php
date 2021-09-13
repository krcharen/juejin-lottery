<?php
/**
 * 掘金签到抽奖的PHP版本的实现；
 * 作者：Charen
 * 日期：2021.9.13
 * GitHub地址：https://github.com/krcharen/juejin-lottery
 * Version 1.0.0
 */

class Juejin
{

    /**
     * 脚本开始时（微秒）
     * @var float|string
     */
    private $starttime;

    /**
     * 脚本结束时（微秒）
     * @var
     */
    private $endtime;

    /**
     * 总矿石数
     * @var
     */
    private $ore;

    /**
     * 单次抽奖矿石耗费数量，200矿石/次
     * @var int
     */
    private $fee = 200;

    /**
     * 获取账户下总的矿石数API
     * @var string
     */
    private $ore_url = 'https://api.juejin.cn/growth_api/v1/get_cur_point?aid=&uuid=';

    /**
     * 开始抽奖API
     * @var string
     */
    private $lottery_url = 'https://api.juejin.cn/growth_api/v1/lottery/draw?aid=&uuid=';

    /**
     * Cookie设置（需手动填写）
     * @var string
     */
    private $cookie = '';

    /**
     * @var
     */
    private $statisticsText = '';

    /**
     * 奖品列表（需要根据官方动态调整具体奖品）
     * @var string[]
     */
    private static $lottery_lists = [
        6981716980386496552 => ['66矿石', 0],
        6981716405976743943 => ['Bug', 0],
        7007250710618177539 => ['抖音探月月饼', 0],
        6993211005295656975 => ['随机限量徽章', 0],
        7007250928470327334 => ['抖音中秋月饼', 0],
        7007250996757807135 => ['星巴克月饼', 0],
        7007251166694211624 => ['字节中秋礼盒', 0],
        7007251646212374561 => ['希尔顿月饼', 0],
    ];

    /**
     * Juejin constructor.
     */
    public function __construct()
    {
        $this->environment();

        $this->starttime = microtime(true);
        $this->totalOres();
        $this->startLottery();
    }

    /**
     * 脚本运行环境
     */
    private function environment()
    {
        if (PHP_SAPI !== 'cli') exit('请在CLI下运行该程序！');
    }

    /**
     * 总矿石数
     */
    private function totalOres()
    {
        $context = stream_context_create($this->requestOptions('GET'));
        $result = @file_get_contents($this->ore_url, false, $context);
        $data = json_decode($result, true);
        if ($data['data'] < $this->fee) exit('oh~矿石不足，请继续每日签到吧！');
        $this->ore = $data['data'];
    }

    /**
     * 开始抽奖
     */
    private function startLottery()
    {
        $callTimes = intval($this->ore / $this->fee);
        $context = stream_context_create($this->requestOptions('POST'));
        $i = 0;

        while ($i <= $callTimes) {
            $result = @file_get_contents($this->lottery_url, false, $context);
            $this->statistics(json_decode($result, true));
            $i++;
        }

        foreach (self::$lottery_lists as $lottery_id => $lottery_sub) {
            if ($lottery_sub[1] !== 0) {
                $this->statisticsText .= $lottery_sub[0] . '：' . $lottery_sub[1] . '；';
            }
        }
    }

    /**
     * @param array $result
     */
    private function statistics(array $result)
    {
        $data = $result['data'];
        echo '抽到【' . $data['lottery_name'] . '】' . '   ' . '数量+1' . PHP_EOL;
        self::$lottery_lists[intval($data['lottery_id'])][1]++;
    }

    /**
     * @param string $method
     * @return array[]
     */
    private function requestOptions($method = 'GET')
    {
        return [
            'http' => [
                'method' => $method,
                'header' => [
                    'Accept: */*',
                    'Content-Type: application/json',
                    'Cookie: ' . $this->cookie
                ]
            ]
        ];
    }

    /**
     * 输出文本内容
     */
    public function __destruct()
    {
        $this->endtime = microtime(true);

        $text = PHP_EOL;
        $text .= '[本次运行：' . round($this->endtime - $this->starttime, 3) . ' s]';
        $text .= ' 总矿石数：' . $this->ore;
        $text .= PHP_EOL;
        $text .= '本次抽奖统计：' . $this->statisticsText;
        $text .= PHP_EOL;
        echo $text;
    }
}

new Juejin();