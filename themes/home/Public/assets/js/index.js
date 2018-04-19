  // 右侧边飞机连接显示隐藏
  $(function() {
      // @ 给窗口加滚动条事件
      //$(window).scroll(function() {
          // 获得窗口滚动上去的距离
          //var ling = $(document).scrollTop();
          // 如果滚动距离大于500的时候让滚动框出来
          //if (ling > 1000) {
             // $('#FJ').show();
          //}
          //if (ling > 8800 || ling < 1534) {
              // $('#FJ').css('display','none');  // @ 这一句和下一句效果一样。
              //$('#FJ').show();
          //}
      //});
      // 右侧边飞机连接
      $("#FB").click(function() {
          $("body,html").animate({
              scrollTop: 0
          }, 500);
      });
      $("#SUO").click(function() {
          $("body,html").animate({
              scrollTop: 0
          }, 500);
          if ($('.DL').is(':hidden')) { //如果当前隐藏  
              $('.DL').show(); //那么就显示div  
          } else { //否则  
              $('.DL').hide(); //就隐藏div  
          }
      });
      // 点击飞机的X隐藏
      $('#guanbi').click(function() {
          $('#FJ').hide();
      })

          // 点击产品隐藏信息的X隐藏
      $('.YC .cha').click(function() {
              $('.YC').hide();
          })
          //会员中心的登录
      $('#DL').click(function() { //点击标签  
          if ($('.DL').is(':hidden')) { //如果当前隐藏  
              $('.DL').show(); //那么就显示div  
          } else { //否则  
              $('.DL').hide(); //就隐藏div  
          }
      })







      // 产品隐藏信息
      $('.lis1 .ZZPM_list_JG').click(function() { //点击标签  
          if ($('.lis1 .YC').is(':hidden')) { //如果当前隐藏  
              $('.lis1 .YC').show(); //那么就显示div  
          } else { //否则  
              $('.lis1 .YC').hide(); //就隐藏div  
          }
      })
      $('.lis2 .ZZPM_list_JG').click(function() { //点击标签  
          if ($('.lis2 .YC').is(':hidden')) { //如果当前隐藏  
              $('.lis2 .YC').show(); //那么就显示div  
          } else { //否则  
              $('.lis2 .YC').hide(); //就隐藏div  
          }
      })
      $('.lis3 .ZZPM_list_JG').click(function() { //点击标签  
          if ($('.lis3 .YC').is(':hidden')) { //如果当前隐藏  
              $('.lis3 .YC').show(); //那么就显示div  
          } else { //否则  
              $('.lis3 .YC').hide(); //就隐藏div  
          }
      })
      $('.lis4 .ZZPM_list_JG').click(function() { //点击标签  
          if ($('.lis4 .YC').is(':hidden')) { //如果当前隐藏  
              $('.lis4 .YC').show(); //那么就显示div  
          } else { //否则  
              $('.lis4 .YC').hide(); //就隐藏div  
          }
      })
      $('.lis5 .ZZPM_list_JG').click(function() { //点击标签  
          if ($('.lis5 .YC').is(':hidden')) { //如果当前隐藏  
              $('.lis5 .YC').show(); //那么就显示div  
          } else { //否则  
              $('.lis5 .YC').hide(); //就隐藏div  
          }
      })
      $('.lis6 .ZZPM_list_JG').click(function() { //点击标签  
          if ($('.lis6 .YC').is(':hidden')) { //如果当前隐藏  
              $('.lis6 .YC').show(); //那么就显示div  
          } else { //否则  
              $('.lis6 .YC').hide(); //就隐藏div  
          }
      })
      $('.lis7 .ZZPM_list_JG').click(function() { //点击标签  
          if ($('.lis7 .YC').is(':hidden')) { //如果当前隐藏  
              $('.lis7 .YC').show(); //那么就显示div  
          } else { //否则  
              $('.lis7 .YC').hide(); //就隐藏div  
          }
      })
      $('.lis8 .ZZPM_list_JG').click(function() { //点击标签  
          if ($('.lis8 .YC').is(':hidden')) { //如果当前隐藏  
              $('.lis8 .YC').show(); //那么就显示div  
          } else { //否则  
              $('.lis8 .YC').hide(); //就隐藏div  
          }
      })
      $('.lis9 .ZZPM_list_JG').click(function() { //点击标签  
          if ($('.lis9 .YC').is(':hidden')) { //如果当前隐藏  
              $('.lis9 .YC').show(); //那么就显示div  
          } else { //否则  
              $('.lis9 .YC').hide(); //就隐藏div  
          }
      })
      $('.lis10 .ZZPM_list_JG').click(function() { //点击标签  
              if ($('.lis10 .YC').is(':hidden')) { //如果当前隐藏  
                  $('.lis10 .YC').show(); //那么就显示div  
              } else { //否则  
                  $('.lis10 .YC').hide(); //就隐藏div  
              }
          })
          // 点击产品隐藏信息的X隐藏
      $('.YC .cha').click(function() {
          $('.YC').hide();
      })



      //鸽闻天下
      $('.DHLIST #tab1').click(function() { //点击标签  
          $('#tab1').addClass('XUANZHONG');
          $('#tab2').removeClass('XUANZHONG');
          $('#tab3').removeClass('XUANZHONG');
          $('#tab4').removeClass('XUANZHONG');
          $('#tab5').removeClass('XUANZHONG');
          $('#tab6').removeClass('XUANZHONG');
          $('#tab7').removeClass('XUANZHONG');
          $('#tab8').removeClass('XUANZHONG');
          $('#tab9').removeClass('XUANZHONG');
          $('#tab10').removeClass('XUANZHONG');
      })


  });