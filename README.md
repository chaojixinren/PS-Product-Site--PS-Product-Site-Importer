# PS-Product-Site&PS-Product-Site-Importer


> WordPress 插件：用 **“产品目录 + 详情”** 方案快速搭建产品展示站。适配 Avada 等主题；含后台字段、REST 输出、短代码渲染；可选 CSV 批量导入器。

- 主插件下载 
- CSV 导入器

---

## ✨ 功能概览
- 自定义文章类型 **产品**（CPT：`ps_product`）与 **产品分类**（TAX：`ps_category`）
- 后台元字段面板（型号、图库、亮点、场景、参数表、补充，见下方映射）
- REST API：`/wp-json/ps/v1/products`
- 短代码渲染 **产品目录 + 右侧详情**（默认 iframe 隔离，规避主题 CSS/JS 冲突）
- 一键全宽显示与版心宽度可配
- 可选 **CSV 批量导入**（按标题去重/更新）

---

## 🧩 字段映射（后台 → 前端/导入列）

| 后台元字段 | 用途/前端键 |
|---|---|
| `ps_sub` | 型号 / `sub` |
| `ps_img1` | 主图 / `img1`（留空则回退特色图像） |
| `ps_img2` | 图库2 / `A5` |
| `ps_img3` | 图库3 / `A8` |
| `ps_img4` | 图库4 / `A11` |
| `ps_features_title` | 亮点标题 / `A6` |
| `ps_features_lines` | 亮点条目（一行一条）/ `A7` |
| `ps_scenarios_title` | 场景标题 / `A9` |
| `ps_scenarios_lines` | 场景条目（一行一条）/ `A10` |
| `ps_table1` | 参数表1（HTML `<table>`）/ `table1` |
| `ps_table2` | 参数表2（HTML `<table>`）/ `table2` |
| `ps_extra_text` | 补充1 / `A12` |
| `ps_extra2` | 补充2 / `Unnamed:_17` |
| `ps_extra3` | 补充3 / `Unnamed:_18` |

> 文章正文即 `desc`；分类对应 `内容栏目`。

---

## 🔌 REST 接口
- **URL**：`/wp-json/ps/v1/products`
- **返回**：数组，每项含 `title, sub, desc, 内容栏目, img1, A5, A8, A11, A6, A7, A9, A10, A12, Unnamed:_17, Unnamed:_18, table1, table2`。

---

## 🧱 短代码用法

基础用法（推荐全宽）：
```text
[product_catalog fullwidth="1" maxwidth="1280"]


# PS Product Site 插件说明

> 为 WordPress 快速搭建“**产品目录 + 详情**”系统，适配 Avada 等主题；支持后台字段、REST 输出、短代码渲染与 CSV 批量导入。

---

## ✨ 功能概览

* 自定义文章类型 **产品**（CPT：`ps_product`）与 **产品分类**（TAX：`ps_category`）
* 后台元字段面板（型号、图库、亮点、场景、参数表、补充）
* REST API：`/wp-json/ps/v1/products`
* 短代码渲染前端目录页，**默认 iframe 隔离**，规避主题 CSS/JS 冲突
* 全宽显示与版心宽度可配
* **CSV 批量导入**（按标题去重更新）

---

## 🧩 字段映射（后台 → 前端/导入列）

| 后台元字段                | 用途/前端键                         |
| -------------------- | ------------------------------ |
| `ps_sub`             | 型号 / `sub`                     |
| `ps_img1`            | 主图 / `img1`（留空则回退特色图像）         |
| `ps_img2`            | 图库2 / `A5`                     |
| `ps_img3`            | 图库3 / `A8`                     |
| `ps_img4`            | 图库4 / `A11`                    |
| `ps_features_title`  | 亮点标题 / `A6`                    |
| `ps_features_lines`  | 亮点条目（一行一条）/ `A7`               |
| `ps_scenarios_title` | 应用场景标题 / `A9`                  |
| `ps_scenarios_lines` | 应用场景条目（一行一条）/ `A10`            |
| `ps_table1`          | 参数表1（HTML `<table>`）/ `table1` |
| `ps_table2`          | 参数表2（HTML `<table>`）/ `table2` |
| `ps_extra_text`      | 补充1 / `A12`                    |
| `ps_extra2`          | 补充2 / `Unnamed:_17`            |
| `ps_extra3`          | 补充3 / `Unnamed:_18`            |

> 文章正文即 `desc`；分类为 `内容栏目`。

---

## 🔌 REST 接口

* **URL**：`/wp-json/ps/v1/products`
* **返回**：数组，每项含 `title, sub, desc, 内容栏目, img1, A5, A8, A11, A6, A7, A9, A10, A12, Unnamed:_17, Unnamed:_18, table1, table2`。

---

## 🧱 短代码用法

基础用法（推荐全宽）：

```text
[product_catalog fullwidth="1" maxwidth="1280"]
```

可选参数：

* `mode="iframe"`（默认）：iframe 隔离渲染，避免主题冲突
* `fullwidth="1"`：突破容器限制并居中
* `maxwidth="1280"`：全宽模式版心宽度
* `minheight="600"`：iframe 初始高度

> 已内置**自适应高度**，修复“滚动条无限增长”问题（去抖+阈值+实例ID绑定）。

---

## 🚀 安装 & 使用

1. 插件 → 安装插件 → 上传并启用 **ps-product-site-v1.x.x.zip**
   激活后会自动生成页面 **“产品目录”**（内容为 `[product_catalog]`）。
2. 后台 → **产品**：新增/编辑产品，或使用导入器批量导入。
3. 在任意页面插入短代码（如上），前端即显示目录 + 详情。

---

## 📥 CSV 导入（可选子插件）

1. 上传并启用 **ps-product-site-importer.zip**。
2. 菜单：**产品 → 导入 (CSV)** → 上传 CSV（UTF-8 BOM）。
3. 支持列（按标题去重）：

   ```
   内容栏目,title,desc,sub,img1,A5,A8,A11,table1,table2,A6,A7,A9,A10,A12,Unnamed:_17,Unnamed:_18
   ```
---

## 📝 环境要求

* WordPress 5.8+，PHP 7.4+（推荐 8.x）
* 与 Avada 等主流主题兼容

---

## 🗒️ 版本摘要

* **v1.3.3**：已彻底修复 PHP 的字符串拼接问题，并保留 iframe 隔离与滚动条增长修复
* **v1.3.2**：修复 iframe 高度无限增长；去除轮询，启用容器尺寸+去抖；ID 作用域
* **v1.3.1**：修复字符串拼接与自适应高度注入
* **v1.3.0**：新增 **iframe 隔离模式**
* **v1.2.1**：清理误写的 `str()` 调用
* **v1.2.0**：嵌入自定义前端模板，替换 `data.json` 为 REST
* **v1.1.0**：短代码新增 `fullwidth / maxwidth` 参数
* **v1.0.0**：CPT/分类/短代码/REST 基础能力


