<?php

namespace Tygh\Addons\Helpdesk\Readers;

class ImapReader implements IReader {
    private $mbox='';
    private $htmlmsg = '';
    private $plainmsg = '';
    private $charset = '';
    private $attachments = array();
    private $unread;

    public function setSettings($params) {
        $messages=array();
        $folder="INBOX";
        $this->unread = false;
        imap_timeout( IMAP_OPENTIMEOUT, 3);
        $this->mbox = @imap_open("{$params['host']}{$folder}", $params['login'],$params['password']);
        if ($this->mbox) {
            $arr=imap_search  ($this->mbox, 'UNSEEN');

            if ($arr !== false) {
                foreach ($arr as $i){

                    $headerArr = imap_headerinfo ( $this->mbox, $i);

                    $reply_to = $headerArr->reply_to[0]->mailbox . "@" . $headerArr->reply_to[0]->host;
                    $from = $headerArr->from[0]->mailbox . "@" . $headerArr->from[0]->host;

                    $this->getmsg($i);

                    //imap_setflag_full($this->mbox, $i, "\\Seen");
                    $messages[]=array(
                        'from'=> $reply_to ? $reply_to : $headerArr->sender[0]->mailbox . "@" . $headerArr->sender[0]->host,
                        'to'=> $headerArr->to[0]->mailbox . "@" . $headerArr->to[0]->host,
                        'name'=> ($from == 'manager@cs-market.com') ? $this->decode($headerArr->reply_to[0]->mailbox) : $this->decode($headerArr->sender[0]->personal) ,
                        'subject'=>$this->decode($headerArr->subject),
                        'charset'=>$this->charset,
                        'plain'=>$this->plainmsg,
                        'html'=>$this->htmlmsg,
                        'timestamp' => $headerArr->udate,
                        'attach'=>$this->attachments
                    );
                }

                $this->unread=$messages;
                unset($messages);
            }
            else {$this->unread=false;}
            imap_close($this->mbox);
        } else {
            $this->errors = imap_errors();
        }
    }

    public function getMail(){ return $this->unread;}

    public function getErrors(){
        return $this->errors;
    }

    private function decode($enc){
        $parts = imap_mime_header_decode($enc);
        $str='';

        for ($p=0; $p<count($parts); $p++) {
            $ch=$parts[$p]->charset;
            
            $part=$parts[$p]->text;
            if ($ch!=='default') {
                $str.=mb_convert_encoding($part,'UTF-8',$ch);
            } else {
                $str.=$part;
            }
        }
        return $str;
    }

    private function getmsg($mid) {
        $this->htmlmsg = $this->plainmsg = $this->charset = '';
        $this->attachments = array();

        $s = imap_fetchstructure($this->mbox,$mid);

        if (!empty($s->parts))
            foreach ($s->parts as $partno0=>$p)
                $this->getpart($mid,$p,$partno0+1);
        else {
            $this->getpart($mid,$s,0); 
        }
    }

    private function getpart($mid,$p,$partno) {
        $data = ($partno)? imap_fetchbody($this->mbox,$mid,$partno): imap_body($this->mbox,$mid); 
        if ($p->encoding==4)
            $data = quoted_printable_decode($data);
        elseif ($p->encoding==3)
            $data = base64_decode($data);

        $params = array();
        if ($p->parameters)
            foreach ($p->parameters as $x)
                $params[ strtolower( $x->attribute ) ] = $x->value;
        if (!empty($p->dparameters))
            foreach ($p->dparameters as $x)
                $params[ strtolower( $x->attribute ) ] = $x->value;

        if (isset($params['filename']) || isset($params['name'])) {
            $filename = ($params['filename'])? $params['filename'] : $params['name'];
            $filename = $this->decode($filename);
            $this->attachments[$filename] = $data;
        }
        elseif ($p->type==0 && $data) {
            if (strtolower($p->subtype)=='plain') 
                $this->plainmsg .= $data;
            else
                $this->htmlmsg .= $data ."<br><br>";
            $this->charset = $params['charset']; 
        }
        elseif ($p->type==2 && $data) {
            $this->plainmsg .= $data;
        }

        if (!empty($p->parts)) {
            foreach ($p->parts as $partno0=>$p2)
                $this->getpart($mid,$p2,$partno.'.'.($partno0+1)); 
        }
    }
}
