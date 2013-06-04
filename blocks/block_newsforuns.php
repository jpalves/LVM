<?php 
//Autor jpalves
require_once($CFG->dirroot.'/mod/forum/lib.php'); 

class block_newsforuns extends block_base {
        
        function init(){//inicialização
                $this->title   = get_string('pluginname','block_newsforuns');//ficheiro da língua
        }
		//
		function specialization(){
			//actualiza sempre o modinfo
			if(!isset($this->modinfo)){
				if(!$this->modinfo = get_fast_modinfo($this->page->course)) return;
				if(empty($this->modinfo->instances['forum'])) return;
				
				foreach($this->modinfo->instances['forum'] as $forum){
					$this->fname[$forum->instance] = $forum->name;
				}
			}
			//echo 'corre sepcial';
			if(empty($this->config->title)){
				if(isset($this->config->fname)){//em teste
					if(isset($this->fname[$this->config->fname])){
						$this->config->title = $this->fname[$this->config->fname];
						$this->title         = $this->config->title;
					} else {
						$this->title         = get_string('erroforum','block_newsforuns');
						$this->config->title = '';
					}
				} else {
					$this->title = get_string('pluginname','block_newsforuns');
				}
			} else {
				if(isset($this->fname[$this->config->fname])){
					$this->title = $this->config->title;
				} else {
					$this->title         = get_string('erroforum','block_newsforuns');
					$this->config->title = '';
				}
			}
		}
		
		function instance_allow_multiple() {
			return true;
		}
		
		//devolve conteúdo ao moodle
        function get_content() {
			global $CFG, $USER, $DB;
			
			//echo 'Corre content';
			if ($this->content !== NULL){
				return $this->content;
            }
			$this->content = new stdClass;
			$this->content->footer ='';
			
			$text = '';
			if(!isset($this->config->fname)) return '';
			if(!isset($this->fname[$this->config->fname])) return '';
		    //echo 'passei';
			if(!$forums = $DB->get_records_select("forum", "course = ?", array($this->page->course->id), "id ASC")) return '';
			$forum = $forums[$this->config->fname];
			//print_r($forum);
			
			
			$cm           = $this->modinfo->instances['forum'][$forum->id];
			$context      = get_context_instance(CONTEXT_MODULE,$cm->id);
			
			//utilizador tem de ter permissões para visualizar a discussão
			if (!has_capability('mod/forum:viewdiscussion', $context)) {
                return '';
            }
			
			$groupmode    = groups_get_activity_groupmode($cm);
			$currentgroup = groups_get_activity_group($cm, true);
            
			//testar como aluno
			if (forum_user_can_post_discussion($forum, $currentgroup, $groupmode, $cm, $context)) {
				$text .= '<div class="newlink"><a href="'.$CFG->wwwroot.'/mod/forum/post.php?forum='.$forum->id.'">'.
						  get_string('addanewtopic', 'forum').'</a>...</div>';
			}
			
			//busca todos os assuntos em que é permitido ver
			if (! $discussions = forum_get_discussions($cm, 'p.modified DESC', false, 
														$currentgroup, $this->config->itens) ) {
				if($forum->type=='news') $text .= '('.get_string('nonews', 'forum').')';
				else $text .= '('.get_string('nodiscussions', 'forum').')';
				$this->content->text = $text;
				return $this->content;
			}
            
			$strftimerecent = get_string('strftimerecent');
			$strmore        = get_string('more', 'forum');
			
			
			$text .= "\n<ul class=\"unlist\">\n";
			foreach ($discussions as $discussion) {
				$discussion->subject = $discussion->name;

				$discussion->subject = format_string($discussion->subject, true, $forum->course);

				$text .= '<li class="post">'.
					     '<div class="head">'.
						 '<div class="date">'.userdate($discussion->modified, $strftimerecent).'</div>'.
						 '<div class="name">'.fullname($discussion).'</div></div>'.
						 '<div class="info">'.$discussion->subject.' '.
                         '<a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$discussion->discussion.'">'.
                         $strmore.'...</a></div>'.
                         "</li>\n";
            }
            $text .= "</ul>\n";

            $this->content->text = $text;
			
			$this->content->footer = '<a href="'.$CFG->wwwroot.'/mod/forum/view.php?f='.$forum->id.'">'.
                                      get_string('oldertopics', 'forum').'</a> ...';

            if(isset($CFG->enablerssfeeds) && isset($CFG->forum_enablerssfeeds) &&
                $CFG->enablerssfeeds && $CFG->forum_enablerssfeeds && $forum->rsstype && $forum->rssarticles) {
                require_once($CFG->dirroot.'/lib/rsslib.php');   // We'll need this
                if ($forum->rsstype == 1) {
                    $tooltiptext = get_string('rsssubscriberssdiscussions','forum');
                } else {
                    $tooltiptext = get_string('rsssubscriberssposts','forum');
                }
				if (!isloggedin()) {
                    $userid = 0;
				} else {
                    $userid = $USER->id;
				}
				//existe um bug no bloco das útimas notícias estão a ir buscar o context errado 
				//é $context->id; e não $this->page->context->id;
                $this->content->footer .= '<br /><br />RSS Feed: '.rss_get_link($context->id, $userid, 'mod_forum', $forum->id, $tooltiptext);
            }
            return $this->content;
        }
        //função de configuração (edit)
        function instance_allow_config() {
            return true;
        }
}

?>