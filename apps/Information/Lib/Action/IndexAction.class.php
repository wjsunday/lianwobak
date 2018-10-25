<?php

namespace Apps\Information\Controller;

defined('SITE_PATH') || exit('Forbidden');

use Apps\Information\Common;
use Action                         as Controller;
use Apps\Information\Model\Top     as TopModel;
use Apps\Information\Model\Cate    as CateModel;
use Apps\Information\Model\Subject as SubjectModel;

/**
 * 资讯首页控制器
 *
 * @package Apps\Information\Controller\Index
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Index extends Controller
{
    /**
     * 配置
     *
     * @var array
     **/
    protected $conf;

    /**
     * 初始化控制器
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function _initialize()
    {
        Common::setHeader('text/html', 'utf-8');
        array_push($this->appCssList, 'css/css.css');
        $this->conf = model('Xdata')->get('Information_Admin:config');
    }

    /**
     * 首页方法
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function index()
    {
        /* # SEO */
        $this->setTitle('资讯');
        $this->setKeywords('ThinkSNS,T4,资讯,Information');

        /* 添加必要JS */
        array_push($this->appJsList, 'js/jquery.SuperSlide.2.1.1.js');
        array_push($this->appJsList, 'js/index.js');

        /* # 幻灯片 */
        $this->assign('slide', TopModel::getInstance()->getIndexList());

        /* # 文章分类 */
        $cid = intval(Common::getInput('cid', 'get'));
        CateModel::getInstance()->setId($cid)->hasById() || $cid = 0;
        $this->assign('cates', CateModel::getInstance()->get4Rank());
        $this->assign('cid', $cid);

        /* # 主题列表 */
        $list = SubjectModel::getInstance()->setCate($cid)->getList(20);
        $subjects = array();
        foreach ($list['data'] as $subject) {
            preg_match_all('/\<img(.*?)src\=\"(.*?)\"(.*?)\/?\>/is', $subject['content'], $image);
            $image = $image[2];
            if ($image && is_array($image) && count($image) >= 1) {
                $image = $image[array_rand($image)];
                if (!preg_match('/https?\:\/\//is', $image)) {
                    $image = parse_url(SITE_URL, PHP_URL_SCHEME).'://'.parse_url(SITE_URL, PHP_URL_HOST).'/'.$image;
                }
            }
            $subject['commentNum'] = $this->_getComentNum($subject['id']);
            $subject['image'] = $image;
            array_push($subjects, $subject);
        }
        $this->assign('pageHtml', $list['html']);
        $this->assign('subjects', $subjects);
        unset($list, $subjects, $subject, $image);

        /* 热门推荐 */
        $this->_subjectTop();

        /* 热门评论 */
        $this->_comentTop();

        $this->display('list');
    }

    /**
     * 阅读主题
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function read()
    {
        $id = intval(Common::getInput('id', 'get'));
        /* 判断是否不存在 */
        if (!SubjectModel::getInstance()->setId($id)->has()) {
            $this->error('该主题不存在！');

        /* 判断是否是预发布 */
        } elseif (SubjectModel::getInstance()->hasIsPre()) {
            $this->error('该主题还未审核通过！');
        }
        $data = SubjectModel::getInstance()->getSubject();
        SubjectModel::getInstance()->setHits($data['hits'] + 1)->update();
        $data['hits'] || $data['hits'] = 0;
        $data['commentNum'] = $this->_getComentNum($data['id']);
        $this->assign('subjectData', $data);

        /* seo */
        $this->setTitle($data['subject']);
        $this->setKeywords('ThinkSNS,T4,资讯,Information');
        $this->setDescription($data['abstract']);

    /* 热门推荐 */
    $this->_subjectTop();

    /* 热门评论 */
    $this->_comentTop();

        $this->display('read');
    }

  /**
   * 投稿
   *
   * @author Seven Du <lovevipdsw@vip.qq.com>
   **/
  public function release()
  {
      /* seo */
    $this->setTitle('资讯 - 投稿');
      $this->setKeywords('ThinkSNS,T4,资讯,Information');
      $this->assign('guide', $this->conf['guide']);
      $this->assign('cates', CateModel::getInstance()->get4Rank());

      array_push($this->appJsList, 'js/release.js');

      $this->display('release');
  }

  /**
   * 处理投稿
   *
   * @author Seven Du <lovevipdsw@vip.qq.com>
   **/
  public function postRelease()
  {
      list($subject, $cid, $abstract, $content) = Common::getInput(array('subject', 'cid', 'abstract', 'content'), 'post');
      SubjectModel::getInstance()->setCate($cid)
                               ->setSubject($subject)
                               ->setAbstract($abstract)
                               ->setContent($content)
                               ->setAuthor($this->mid)
                               ->setCTime()
                               ->setRTime()
                               ->setIsPre(true);
      if (SubjectModel::getInstance()->add()) {
          $this->assign('jumpUrl', U('Information/Index/index'));
          $this->success('投稿成功', true);
      }
      $this->error(SubjectModel::getInstance()->getError(), true);
  }

    /**
     * 获取热门评论的主题
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    private function _comentTop($num)
    {
        $where = '`is_del` = 0 AND `app` = \'Information\' AND `table` = \'%s\' AND `row_id` > 0';
        $where = sprintf($where, 'information_list');
        $time = $this->conf['commentHotTime'];
        if ($time > 0) {
            $time = intval($time);
            $time = 60 * 60 * 24 * $time;
            $time = time() - $time;
            $time = intval($time);
            $where .= ' AND `ctime` > '.$time;
        }
        $ids = model('Comment')->where($where)
                               ->order('`num` DESC')
                               ->field('`row_id`, count(`comment_id`) as `num`')
                           ->group('`row_id`')
                           ->limit(5)
                           ->select();
        foreach ($ids as $key => $value) {
            $ids[$key] = $value['row_id'];
        }
        $ids = implode(',', $ids);
        $list = SubjectModel::getInstance()->where('`id` IN ('.$ids.') AND `isDel` != 1 AND `isPre` != 1')
                                       ->field('`id`,`subject`,`content`')
                                       ->select();

        foreach ($list as $key => $subject) {
            preg_match_all('/\<img(.*?)src\=\"(.*?)\"(.*?)\/?\>/is', $subject['content'], $image);
            $image = $image[2];
            if ($image && is_array($image) && count($image) >= 1) {
                $image = $image[array_rand($image)];
                if (!preg_match('/https?\:\/\//is', $image)) {
                    $image = parse_url(SITE_URL, PHP_URL_SCHEME).'://'.parse_url(SITE_URL, PHP_URL_HOST).'/'.$image;
                }
            }
            $subject['image'] = $image;
            unset($subject['content']);
            $list[$key] = $subject;
        }
        $this->assign('commentHots', $list);
        unset($list, $ids, $where, $time, $image, $subject);
    }

    /**
     * 热门推荐
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    private function _subjectTop()
    {
        $num = 9; // 获取数量
        $this->assign('subjectTops', SubjectModel::getInstance()->getHot($num, $this->conf['hotTime']));
    }

    /**
     * 获取评论数
     *
     * @param  int $sid 主题ID
     * @return int 评论数
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    private function _getComentNum($sid)
    {
        $where = '`is_del` = 0 AND `app` = \'Information\' AND `table` = \'%s\' AND `row_id` = %d';
        $where = sprintf($where, 'information_list', intval($sid));

        return model('Comment')->where($where)->field('comment_id')->count();
    }
} // END class Index extends Controller
class_alias('Apps\Information\Controller\Index', 'IndexAction');
