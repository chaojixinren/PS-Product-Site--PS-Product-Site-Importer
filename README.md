# PS-Product-Site & PS-Product-Site-Importer

> WordPress 插件：用 **“产品目录 + 详情”** 方案快速搭建产品展示站。适配 Avada 等主题；含后台字段、REST 输出、短代码渲染；可选 CSV 批量导入器。

* **主插件下载**：`ps-product-site-v1.x.x.zip`
* **CSV 导入器**：`ps-product-site-importer.zip`

---

## ✨ 功能概览

* 自定义文章类型 **产品**（CPT：`ps_product`）与 **产品分类**（TAX：`ps_category`）
* 后台元字段面板（型号、图库、亮点、场景、参数表、补充等）
* REST API：`/wp-json/ps/v1/products`
* 短代码渲染 **产品目录 + 右侧详情**（默认使用 iframe 隔离，规避主题 CSS/JS 冲突）
* 一键全宽显示与版心宽度可配
* 可选 **CSV 批量导入**（按标题去重/更新）

---

## 🧩 字段映射（后台 → 前端/导入列）

| 后台元字段                | 用途 / 前端键                       |
| -------------------- | ------------------------------ |
| `ps_sub`             | 型号 / `sub`                     |
| `ps_img1`            | 主图 / `img1`（留空则回退特色图像）         |
| `ps_img2`            | 图库2 / `A5`                     |
| `ps_img3`            | 图库3 / `A8`                     |
| `ps_img4`            | 图库4 / `A11`                    |
| `ps_features_title`  | 亮点标题 / `A6`                    |
| `ps_features_lines`  | 亮点条目（一行一条）/ `A7`               |
| `ps_scenarios_title` | 场景标题 / `A9`                    |
| `ps_scenarios_lines` | 场景条目（一行一条）/ `A10`              |
| `ps_table1`          | 参数表1（HTML `<table>`）/ `table1` |
| `ps_table2`          | 参数表2（HTML `<table>`）/ `table2` |
| `ps_extra_text`      | 补充1 / `A12`                    |
| `ps_extra2`          | 补充2 / `Unnamed:_17`            |
| `ps_extra3`          | 补充3 / `Unnamed:_18`            |

> 文章正文即 `desc`；分类对应 “内容栏目”。

---

## 🔌 REST 接口

* **URL**：`/wp-json/ps/v1/products`
* **返回**：数组。每项包含：

  ```
  title, sub, desc, 内容栏目, img1, A5, A8, A11,
  A6, A7, A9, A10, A12, Unnamed:_17, Unnamed:_18,
  table1, table2
  ```

---

## 🧱 短代码用法

**基础用法（推荐全宽）：**

```
[product_catalog fullwidth="1" maxwidth="1280"]
```

**参数说明：**

* `mode="iframe"`（默认）：iframe 隔离渲染，避免主题冲突
* `fullwidth="1"`：突破主题容器限制并居中
* `maxwidth="1280"`：全宽模式下的版心宽度
* `minheight="600"`：iframe 初始高度

> 已内置 **自适应高度**（去抖 + 阈值 + 实例 ID 绑定），修复“滚动条无限增长”问题。

---

## 🚀 安装与使用

1. **安装主插件**
   后台 → 插件 → 安装插件 → 上传 `ps-product-site-v1.x.x.zip` 并启用。
   激活后自动生成页面 **“产品目录”**（内容为 `[product_catalog]`）。

2. **管理内容**
   后台 → **产品**：新增/编辑产品，或启用导入器后批量导入。

3. **前端嵌入**
   在任意页面插入上面的短代码（建议全宽参数），即可显示目录 + 详情。

---

## 📥 CSV 导入（可选子插件）

1. 启用 `ps-product-site-importer.zip`
2. 菜单：**产品 → 导入 (CSV)** → 上传 CSV（UTF-8 BOM）
3. **列头支持（按标题去重/更新）**：

   ```
   内容栏目,title,desc,sub,img1,A5,A8,A11,table1,table2,
   A6,A7,A9,A10,A12,Unnamed:_17,Unnamed:_18
   ```

---

## 📝 环境要求

* WordPress 5.8+
* PHP 7.4+（推荐 8.x）
* 与 Avada 等主流主题兼容

---

## 🗒️ 版本摘要
* **v1.4.7**：新增URL参数自动搜索功能，支持?product=xxx参数直接搜索并显示产品
* **v1.4.6**：新增桌面端悬停卡片动画效果，移动端保持列表布局
* **v1.4.5**：产品列表默认隐藏，仅在用户搜索时显示匹配结果
* **v1.4.4**：在参数表下面新增“Applications（A6/A7）”与“Product Portfolio（A9/A10）”两块，分别配图优先使用 A8 与 A11（含合理回退）。
* **v1.4.3**：修复蓝色卡片右侧主图显示（按 img1→A5→A8→A11 顺序回退）
* **v1.4.2**：解决 v1.4.1 中将 PHP 字符串拼接误写为 + 导致的致命错误
* **v1.4.1**：移除“全部/未分类”分类Chip与分类徽标；保留并强化参数表（table1/table2）卡片，支持两标签切换；搜索可命中参数表文本。
* **v1.4.0**：部产品条 + 蓝色大卡片样式，仅一张图片，搜索支持标题/型号/描述/亮点/应用场景/参数表等
* **v1.3.5**：自动兼容 /wp-json/... 和 ?rest_route=... 两种 REST 入口
* **v1.3.4**：前端不再去请求 data.json；保持 iframe 隔离 + 自适应高度稳定
* **v1.3.3**：彻底修复 PHP **字符串拼接**问题；保留 iframe 隔离与滚动条增长修复
* **v1.3.2**：修复 iframe 高度无限增长；移除轮询，采用容器尺寸侦测 + 去抖；实例 ID 作用域隔离
* **v1.3.1**：修复字符串拼接与自适应高度注入
* **v1.3.0**：新增 **iframe 隔离模式**
* **v1.2.1**：清理误写的 `str()` 调用
* **v1.2.0**：内置前端模板，改用 REST 替代 `data.json`
* **v1.1.0**：短代码新增 `fullwidth / maxwidth` 参数
* **v1.0.0**：CPT / 分类 / 短代码 / REST 基础能力

---

## ❓常见问题（FAQ）

* **Avada 样式冲突？**
  默认 `mode="iframe"` 已隔离；如需同域渲染，先确认主题 CSS 命名空间并适配。

* **滚动条异常增长？**
  1.3.2+ 版本已修复（去抖、阈值、实例 ID 绑定）。确保页面未注入其他高度轮询脚本。

* **主图缺失？**
  若 `ps_img1` 为空，将自动回退至该文章的特色图像（Featured Image）。
